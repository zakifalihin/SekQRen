<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiGuruController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\AbsensiSiswaController;

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
    Route::get('/mapel/kelas/{kelas_id}', [JadwalController::class, 'getMataPelajaranByKelas']);
});

// ABSENSI GURU (scan QR)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/guru/scan-qr', [AbsensiGuruController::class, 'scanQr']);
    Route::get('/absen/validate', [AbsensiGuruController::class, 'validateToken'])->name('absen.validate');


    
    // ➡️ PERBAIKAN PATH JADWAL MAPEL
    // Path baru yang sesuai dengan ApiService: /api/guru/kelas/{id_kelas}/mapel
    Route::get('/guru/kelas/{kelas_id}/mapel', [JadwalController::class, 'getMataPelajaranByKelas']);


    // ➡️ FUNGSI ABSENSI SISWA (TASK A2 & A3)
    // A2: Memulai sesi absensi dan mendapatkan token sesi
    Route::post('/absensi/start', [AbsensiSiswaController::class, 'startAbsensiSession']);
    // A3: Memproses scan QR siswa
    Route::post('/absensi/scan', [AbsensiSiswaController::class, 'processScanSiswa']);
});