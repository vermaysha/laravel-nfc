<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\NfcCard;
use App\Services\NfcSecurityService;
use App\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NfcController extends Controller
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
     * Write data to NFC card for an employee
     */
    public function writeCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'card_uid' => 'required|string|unique:nfc_cards,card_uid',
            'device_fingerprint' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($validated['employee_id']);

            // Check if employee already has an active card
            if ($employee->nfcCard && $employee->nfcCard->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already has an active NFC card'
                ], 400);
            }

            // Validate device fingerprint
            $deviceValidation = $this->deviceFingerprintService->validateDevice($validated['device_fingerprint']);

            if (!$deviceValidation['is_trusted'] && $deviceValidation['risk_level'] === 'high') {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not trusted for card creation'
                ], 403);
            }

            // Create secure card data
            $secureData = $this->nfcSecurityService->createSecureCardData($employee, $validated['card_uid']);

            // Create NFC card record
            $nfcCard = NfcCard::create([
                'employee_id' => $employee->id,
                'card_uid' => $validated['card_uid'],
                'public_key' => $secureData['public_key'],
                'encrypted_data' => $secureData['encrypted_data'],
                'signature' => $secureData['signature'],
                'issued_at' => now(),
                'expires_at' => now()->addYear(),
            ]);

            // Create NDEF payload for writing to card
            $ndefPayload = $this->nfcSecurityService->createNdefPayload($secureData);

            DB::commit();

            Log::info('NFC card created', [
                'employee_id' => $employee->id,
                'card_uid' => $validated['card_uid'],
                'device_fingerprint' => $deviceValidation['fingerprint']->fingerprint_hash
            ]);

            return response()->json([
                'success' => true,
                'message' => 'NFC card data generated successfully',
                'data' => [
                    'nfc_card' => $nfcCard,
                    'ndef_payload' => $ndefPayload,
                    'employee' => $employee,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('NFC card creation failed', [
                'error' => $e->getMessage(),
                'employee_id' => $validated['employee_id'] ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create NFC card: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Read and verify NFC card data
     */
    public function readCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ndef_payload' => 'required|string',
            'card_uid' => 'required|string',
            'device_fingerprint' => 'required|array',
        ]);

        try {
            // Validate device fingerprint
            $deviceValidation = $this->deviceFingerprintService->validateDevice($validated['device_fingerprint']);

            if ($deviceValidation['fingerprint']->isBlocked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device is blocked'
                ], 403);
            }

            // Parse NDEF payload
            $ndefData = $this->nfcSecurityService->parseNdefPayload($validated['ndef_payload']);

            // Verify card using database-stored security data
            $nfcCard = $this->nfcSecurityService->verifyCardFromNdef($ndefData);

            if (!$nfcCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or inactive NFC card'
                ], 400);
            }

            // Check for cloned card
            if ($this->nfcSecurityService->detectClonedCard($nfcCard, $ndefData)) {
                $nfcCard->markAsBlocked();
                Log::warning('Potential cloned card detected', [
                    'card_uid' => $validated['card_uid'],
                    'employee_id' => $nfcCard->employee_id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Security violation detected - card blocked'
                ], 403);
            }

            Log::info('NFC card read successfully', [
                'employee_id' => $nfcCard->employee_id,
                'card_uid' => $validated['card_uid'],
                'device_fingerprint' => $deviceValidation['fingerprint']->fingerprint_hash
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Card verified successfully',
                'data' => [
                    'employee' => $nfcCard->employee,
                    'nfc_card' => $nfcCard,
                    'device_fingerprint' => $deviceValidation['fingerprint'],
                    'ndef_data' => $ndefData,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('NFC card verification failed', [
                'error' => $e->getMessage(),
                'card_uid' => $validated['card_uid'] ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Card verification failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Block an NFC card
     */
    public function blockCard(Request $request, NfcCard $nfcCard): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $nfcCard->markAsBlocked();

        Log::warning('NFC card blocked', [
            'card_id' => $nfcCard->id,
            'employee_id' => $nfcCard->employee_id,
            'reason' => $validated['reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'NFC card blocked successfully'
        ]);
    }

    /**
     * Get all NFC cards with their status
     */
    public function index(): JsonResponse
    {
        $nfcCards = NfcCard::with('employee')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $nfcCards
        ]);
    }
}
