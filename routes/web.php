<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NfcController;
use App\Http\Controllers\AttendanceController;

// Main dashboard route
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Views for different sections
Route::get('/employees', function () {
    return view('employees.index');
})->name('employees.index');

Route::get('/nfc-cards', function () {
    return view('nfc-cards.index');
})->name('nfc-cards.index');

Route::get('/attendance', function () {
    return view('attendance.index');
})->name('attendance.index');

Route::get('/nfc-scanner', function () {
    return view('nfc.scanner');
})->name('nfc.scanner');
