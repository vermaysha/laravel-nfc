<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $employees = Employee::with('nfcCard')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'hire_date' => 'required|date',
        ]);

        $employee = Employee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => $employee->load('nfcCard')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $employee->load(['nfcCard', 'attendances' => function($query) {
                $query->with('deviceFingerprint')->latest()->limit(10);
            }])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['sometimes', 'string', Rule::unique('employees')->ignore($employee->id)],
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'hire_date' => 'sometimes|date',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee->load('nfcCard')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        // Soft delete or block the employee instead of hard delete
        $employee->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Employee deactivated successfully'
        ]);
    }

    /**
     * Get employee attendance history
     */
    public function attendanceHistory(Employee $employee, Request $request): JsonResponse
    {
        $query = $employee->attendances()->with(['nfcCard', 'deviceFingerprint']);

        if ($request->has('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        $attendances = $query->orderBy('scanned_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }

    /**
     * Get today's attendance for employee
     */
    public function todaysAttendance(Employee $employee): JsonResponse
    {
        $todaysAttendance = $employee->getTodaysAttendance();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $employee,
                'attendances' => $todaysAttendance,
                'last_check_in' => $employee->getLastCheckIn(),
                'last_check_out' => $employee->getLastCheckOut(),
            ]
        ]);
    }
}
