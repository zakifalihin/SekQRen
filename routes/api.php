<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiGuruController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\LaporanController;

// LOGIN / LOGOUT GURU
Route::post('/guru/login', [AuthController::class, 'login']);
Route::post('/guru/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/guru/dashboard', [GuruController::class, 'dashboard']);
    Route::get('/guru/kelas', [GuruController::class, 'daftarKelas']);
    Route::get('laporan/absensi', [LaporanController::class, 'getLaporanAbsensi']);
    Route::get('laporan/export/excel', [LaporanController::class, 'exportAbsensiExcel']);
    Route::get('laporan/guru', [LaporanController::class, 'getLaporanAbsensiGuru']);
    Route::get('laporan/export/guru', [LaporanController::class, 'exportAbsensiGuruExcel']);
    // ... route lain yang butuh login ...
});

// ABSENSI GURU (scan QR)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/guru/scan-qr', [AbsensiGuruController::class, 'scanQr']);
    Route::get('/absen/validate', [AbsensiGuruController::class, 'validateToken'])->name('absen.validate');
    
});