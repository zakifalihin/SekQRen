<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiGuruController;
use App\Http\Controllers\Api\GuruController; // Controller Utama
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\JadwalController; // Controller lama, mungkin tidak terpakai
use App\Http\Controllers\Api\AbsensiSiswaController;
use App\Http\Controllers\Api\AbsensiController;

// =======================================================
// 1. AUTENTIKASI (PUBLIC)
// =======================================================

// LOGIN / LOGOUT GURU
Route::post('/guru/login', [AuthController::class, 'login']);
Route::post('/guru/logout', [AuthController::class, 'logout']);

// =======================================================
// 2. PROTECTED ROUTES (Requires auth:sanctum)
// =======================================================

Route::middleware('auth:sanctum')->group(function () {

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


    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    */
        // Grouping dengan prefix 'absensi' agar URL rapi
        // URL hasil: /api/absensi/...
        Route::prefix('absensi')->group(function () {
            
            // 1. Memulai Sesi (Guru klik 'Mulai Absen')
            // URL: /api/absensi/start
            // Mengarah ke: startAbsensiSession di Controller
            Route::post('/start', [AbsensiSiswaController::class, 'startAbsensiSession']);

            // 2. Mencatat Kehadiran (Scanner Siswa mengirim data)
            // URL: /api/absensi/catat
            // Mengarah ke: catatKehadiran di Controller
            Route::post('/catat', [AbsensiSiswaController::class, 'catatKehadiran']);

            // ⚠️ Catatan: Route absensi Guru (scan diri sendiri) belum ada method-nya di Controller kamu.
            // Jika nanti method 'absenGuru' sudah dibuat, uncomment baris di bawah ini:
            // Route::post('/guru', [AbsensiSiswaController::class, 'absenGuru']);
        });
        // File: routes/api.php

        // Pastikan URL-nya adalah 'guru/aktivitas-hari-ini'
        Route::get('/guru/aktivitas-hari-ini', [AbsensiController::class, 'getAktivitasHariIni']);
        Route::get('/absensi/export', [AbsensiController::class, 'exportRekap']);
        Route::get('/absensi/detail/{id_jadwal}', [AbsensiController::class, 'getDetailAbsensi']);
        Route::put('/absensi/update/{id}', [AbsensiController::class, 'updateStatusSiswa']);
});