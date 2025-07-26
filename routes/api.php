<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NfcController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Employee management
Route::apiResource('employees', EmployeeController::class);
Route::get('employees/{employee}/attendance', [EmployeeController::class, 'attendanceHistory']);
Route::get('employees/{employee}/today', [EmployeeController::class, 'todaysAttendance']);

// NFC card management
Route::post('nfc/write', [NfcController::class, 'writeCard']);
Route::post('nfc/read', [NfcController::class, 'readCard']);
Route::patch('nfc/{nfcCard}/block', [NfcController::class, 'blockCard']);
Route::get('nfc', [NfcController::class, 'index']);

// Attendance
Route::post('attendance/scan', [AttendanceController::class, 'scan']);
Route::get('attendance', [AttendanceController::class, 'index']);
Route::get('attendance/today', [AttendanceController::class, 'todaysSummary']);
Route::patch('attendance/{attendance}/suspicious', [AttendanceController::class, 'markSuspicious']);
