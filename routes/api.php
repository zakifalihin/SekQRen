<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiGuruController;
use App\Http\Controllers\Api\GuruController; // Controller Utama
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\AbsensiSiswaController;
use App\Http\Controllers\Api\AbsensiController;

// =======================================================
// 1. AUTENTIKASI (PUBLIC)
// =======================================================
// LOGIN
Route::post('/guru/login', [AuthController::class, 'login']);

// =======================================================
// 2. PROTECTED ROUTES (Requires auth:sanctum)
// =======================================================

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/guru/logout', [AuthController::class, 'logout']);

    // --- DASHBOARD & DATA MASTER GURU ---
    Route::get('/guru/dashboard', [GuruController::class, 'dashboard']);
    Route::get('/guru/kelas', [GuruController::class, 'daftarKelas']);
    
    // 🆕 ENDPOINT 1: Ambil Daftar Siswa per Kelas (Untuk QrScannerPage)
    // Controller: GuruController
    Route::get('/guru/kelas/{kelas_id}/siswa', [GuruController::class, 'getSiswaByKelas']);

    // --- DATA JADWAL DAN MAPEL ---
    // 🚀 PERBAIKAN: ENDPOINT 2: Ambil Detail Mata Pelajaran per Kelas
    // Controller: Dipindahkan ke GuruController agar sesuai dengan implementasi sebelumnya.
    Route::get('/guru/kelas/{kelas_id}/mapel', [GuruController::class, 'getMataPelajaranByKelas']);

    // --- ABSENSI GURU (scan QR) ---
    Route::post('/guru/scan-qr', [AbsensiGuruController::class, 'scanQr']);
    Route::get('/absen/validate', [AbsensiGuruController::class, 'validateToken'])->name('absen.validate');

    // --- LAPORAN ---
    Route::get('laporan/absensi', [LaporanController::class, 'getLaporanAbsensi']);
    Route::get('laporan/export/excel', [LaporanController::class, 'exportAbsensiExcel']);
    Route::get('laporan/guru', [LaporanController::class, 'getLaporanAbsensiGuru']);
    Route::get('laporan/export/guru', [LaporanController::class, 'exportAbsensiGuruExcel']);

    // --- AKTIVITAS ---
    Route::post('/absensi/update-status/{id}', [AbsensiSiswaController::class, 'updateStatusSiswa']);

    // AUTH ACTIONS
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user-profile', function (Illuminate\Http\Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    });



    Route::post('/change-password', [AuthController::class, 'changePassword']);

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    */
        Route::prefix('absensi')->group(function () {
            Route::post('/start', [AbsensiSiswaController::class, 'startAbsensiSession']);
            Route::post('/catat', [AbsensiSiswaController::class, 'catatKehadiran']);
        });
        // File: routes/api.php

        // Pastikan URL-nya adalah 'guru/aktivitas-hari-ini'
        Route::get('/guru/aktivitas-hari-ini', [AbsensiController::class, 'getAktivitasHariIni']);
        Route::get('/absensi/export', [AbsensiController::class, 'exportRekap']);
        Route::get('/absensi/detail/{id_jadwal}', [AbsensiController::class, 'getDetailAbsensi']);
        Route::put('/absensi/update/{id}', [AbsensiController::class, 'updateStatusSiswa']);
});

