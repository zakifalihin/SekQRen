<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\AbsensiSiswaController;
use App\Http\Controllers\AbsensiGuruController;


// ========== GURU QR-CODE ==========
Route::middleware('auth:sanctum')->post('/absensi/guru/scan', [AbsensiGuruController::class, 'scanQR']);
Route::post('/absensi/siswa/scan', [AbsensiSiswaController::class, 'scanQR']);


// ========== PUBLIC ROUTES ==========
Route::post('/login/admin', [AuthController::class, 'loginAdmin']);
Route::post('/login/user', [AuthController::class, 'loginUser']);

// ========== PROTECTED ROUTES ==========
// Semua route di sini butuh authentication, entah admin atau user (guru/kepsek)
Route::middleware('auth:sanctum')->group(function () {

    // Profile & Logout bisa diakses oleh semua yang sudah login (admin atau user)
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ===== ADMIN ROUTES =====
    // Gunakan guard admin & middleware admin supaya hanya admin yang bisa akses
    Route::prefix('admin')->middleware(['admin'])->group(function () {
        // Guru
        Route::get('/guru', [AdminController::class, 'indexGuru']);
        Route::post('/guru', [AdminController::class, 'storeGuru']);
        Route::put('/guru/{id}', [AdminController::class, 'updateGuru']);
        Route::delete('/guru/{id}', [AdminController::class, 'destroyGuru']);

        // Siswa
        Route::get('/siswa', [AdminController::class, 'indexSiswa']);
        Route::post('/siswa', [AdminController::class, 'storeSiswa']);
        Route::put('/siswa/{id}', [AdminController::class, 'updateSiswa']);
        Route::delete('/siswa/{id}', [AdminController::class, 'destroySiswa']);
        Route::get('/siswa/{id}', [AdminController::class, 'showSiswa']);

        // Kelas
        Route::get('/kelas', [AdminController::class, 'indexKelas']);
        Route::post('/kelas', [AdminController::class, 'storeKelas']);
        Route::put('/kelas/{id}', [AdminController::class, 'updateKelas']);
        Route::delete('/kelas/{id}', [AdminController::class, 'destroyKelas']);
    });

    // ===== GURU ROUTES =====
    // Gunakan middleware role guru untuk batasi akses ke guru saja
    Route::middleware(['role:guru'])->group(function () {
        Route::get('/guru/kelas', [GuruController::class, 'indexKelas']);
        Route::get('/guru/jadwal', [GuruController::class, 'indexJadwal']);
        Route::post('/guru/absen', [GuruController::class, 'absenSiswa']);
        Route::get('/guru/rekap', [GuruController::class, 'rekapAbsensiKelas']);
    });

    // Kalau ada Kepala Sekolah juga, kamu bisa tambahkan group route seperti ini:
    // Route::middleware(['role:kepsek'])->group(function () {
    //     // route khusus kepsek
    // });

});
