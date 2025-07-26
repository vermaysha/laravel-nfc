<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\NfcCard;
use App\Services\NfcSecurityService;
use App\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    protected NfcSecurityService $nfcSecurityService;
    protected DeviceFingerprintService $deviceFingerprintService;

    public function __construct(
        NfcSecurityService $nfcSecurityService,
        DeviceFingerprintService $deviceFingerprintService
    ) {
        $this->nfcSecurityService = $nfcSecurityService;
        $this->deviceFingerprintService = $deviceFingerprintService;
    }

    /**
     * Record attendance from NFC scan
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ndef_payload' => 'required|string',
            'card_uid' => 'required|string',
            'device_fingerprint' => 'required|array',
            'type' => 'required|in:check_in,check_out',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Validate device fingerprint
            $deviceValidation = $this->deviceFingerprintService->validateDevice($validated['device_fingerprint']);

            if ($deviceValidation['fingerprint']->isBlocked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device is blocked'
                ], 403);
            }

            // Parse and verify NFC card
            $ndefData = $this->nfcSecurityService->parseNdefPayload($validated['ndef_payload']);

            // For NTAG215 compact format, we validate against the database record
            $nfcCard = NfcCard::where('card_uid', $ndefData['card_uid'])
                ->with('employee')
                ->first();

            logger('Found NFC Card', [
                'nfcCard' => $nfcCard ? $nfcCard->toArray() : null,
                'ndefData' => $ndefData,
            ]);

            if (!$nfcCard || !$nfcCard->isActive()) {
                throw new \Exception('Invalid or inactive NFC card');
            }

            // Verify the card data using the stored security data
            $cardData = $this->nfcSecurityService->verifyCardData(
                $nfcCard->encrypted_data,
                $nfcCard->signature,
                $nfcCard->public_key
            );

            // Validate that the NDEF data matches the stored card data
            if ($ndefData['employee_id'] !== $cardData['employee_id'] ||
                $ndefData['card_uid'] !== $cardData['card_uid']) {
                throw new \Exception('Card data mismatch - possible cloned card');
            }

            // Additional validation
            if (!$this->nfcSecurityService->isCardDataValid($cardData, $validated['card_uid'])) {
                throw new \Exception('Card data validation failed');
            }

            $employee = $nfcCard->employee;

            if ($employee->status !== 'active') {
                throw new \Exception('Employee is not active');
            }

            // Check for cloned card
            if ($this->nfcSecurityService->detectClonedCard($nfcCard, $cardData)) {
                $nfcCard->markAsBlocked();
                $deviceValidation['fingerprint']->markAsSuspicious();

                Log::warning('Cloned card detected during attendance scan', [
                    'card_uid' => $validated['card_uid'],
                    'employee_id' => $employee->id,
                    'device_fingerprint' => $deviceValidation['fingerprint']->fingerprint_hash
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Security violation detected - access denied'
                ], 403);
            }

            // Check for duplicate scan (prevent multiple rapid scans)
            $lastScan = Attendance::where('employee_id', $employee->id)
                ->where('type', $validated['type'])
                ->where('scanned_at', '>=', now()->subMinutes(1))
                ->first();

            if ($lastScan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate scan detected - please wait before scanning again'
                ], 400);
            }

            // Validate attendance logic
            $attendanceValidation = $this->validateAttendanceLogic($employee, $validated['type']);
            if (!$attendanceValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $attendanceValidation['message']
                ], 400);
            }

            // Create attendance record
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'nfc_card_id' => $nfcCard->id,
                'device_fingerprint_id' => $deviceValidation['fingerprint']->id,
                'type' => $validated['type'],
                'scanned_at' => now(),
                'location' => $validated['location'] ?? 'Unknown',
                'scan_metadata' => [
                    'card_data_version' => $cardData['version'],
                    'device_risk_level' => $deviceValidation['risk_level'],
                    'scan_timestamp' => now()->timestamp,
                ],
                'status' => $deviceValidation['risk_level'] === 'high' ? 'suspicious' : 'valid',
            ]);

            DB::commit();

            Log::info('Attendance recorded', [
                'attendance_id' => $attendance->id,
                'employee_id' => $employee->id,
                'type' => $validated['type'],
                'device_fingerprint' => $deviceValidation['fingerprint']->fingerprint_hash
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $validated['type'])) . ' recorded successfully',
                'data' => [
                    'attendance' => $attendance->load(['employee', 'nfcCard', 'deviceFingerprint']),
                    'employee' => $employee,
                    'time' => $attendance->scanned_at->format('H:i:s'),
                    'date' => $attendance->scanned_at->format('Y-m-d'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Attendance scan failed', [
                'error' => $e->getMessage(),
                'card_uid' => $validated['card_uid'] ?? null,
                'type' => $validated['type'] ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Attendance scan failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get attendance records
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['employee', 'nfcCard', 'deviceFingerprint']);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('scanned_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }

    /**
     * Get today's attendance summary
     */
    public function todaysSummary(): JsonResponse
    {
        $today = now()->toDateString();

        $summary = [
            'total_employees' => Employee::where('status', 'active')->count(),
            'checked_in' => Attendance::whereDate('scanned_at', $today)
                ->where('type', 'check_in')
                ->distinct('employee_id')
                ->count(),
            'checked_out' => Attendance::whereDate('scanned_at', $today)
                ->where('type', 'check_out')
                ->distinct('employee_id')
                ->count(),
            'suspicious_scans' => Attendance::whereDate('scanned_at', $today)
                ->where('status', 'suspicious')
                ->count(),
        ];

        $recentAttendances = Attendance::with(['employee', 'nfcCard'])
            ->whereDate('scanned_at', $today)
            ->orderBy('scanned_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'recent_attendances' => $recentAttendances,
                'date' => $today,
            ]
        ]);
    }

    /**
     * Mark attendance as suspicious
     */
    public function markSuspicious(Attendance $attendance, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $attendance->markAsSuspicious($validated['reason']);

        Log::warning('Attendance marked as suspicious', [
            'attendance_id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'reason' => $validated['reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked as suspicious'
        ]);
    }

    /**
     * Validate attendance logic (check-in/check-out sequence)
     */
    private function validateAttendanceLogic(Employee $employee, string $type): array
    {
        $lastAttendance = $employee->attendances()
            ->whereDate('scanned_at', today())
            ->orderBy('scanned_at', 'desc')
            ->first();

        if ($type === 'check_in') {
            if ($lastAttendance && $lastAttendance->type === 'check_in') {
                return [
                    'valid' => false,
                    'message' => 'Already checked in today. Please check out first.'
                ];
            }
        } else { // check_out
            if (!$lastAttendance || $lastAttendance->type === 'check_out') {
                return [
                    'valid' => false,
                    'message' => 'No check-in found today. Please check in first.'
                ];
            }
        }

        return ['valid' => true];
    }
}
