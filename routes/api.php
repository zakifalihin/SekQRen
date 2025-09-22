<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiGuruController;
use App\Http\Controllers\Api\GuruController;

// LOGIN / LOGOUT GURU
Route::post('/guru/login', [AuthController::class, 'login']);
Route::post('/guru/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/guru/dashboard', [GuruController::class, 'dashboard']);
    Route::get('/guru/kelas', [GuruController::class, 'daftarKelas']);
    // ... route lain yang butuh login ...
});

// ABSENSI GURU (scan QR)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/guru/scan-qr', [AbsensiGuruController::class, 'scanQr']);
    Route::get('/absen/validate', [AbsensiGuruController::class, 'validateToken'])->name('absen.validate');

});