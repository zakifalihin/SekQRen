<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruController;

// ========== PUBLIC ROUTES ==========
Route::post('/login/admin', [AuthController::class, 'loginAdmin']);
Route::post('/login/user', [AuthController::class, 'loginUser']);

// ========== PROTECTED ROUTES ==========
Route::middleware('auth:sanctum')->group(function () {

    // Profile & Logout (semua user)
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ===== ADMIN ROUTES =====
    // Admin routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/guru', [AdminController::class, 'indexGuru']);
        Route::post('/guru', [AdminController::class, 'storeGuru']);
        Route::put('/guru/{id}', [AdminController::class, 'updateGuru']);
        Route::delete('/guru/{id}', [AdminController::class, 'deleteGuru']);

        Route::get('/siswa', [AdminController::class, 'indexSiswa']);
        Route::post('/siswa', [AdminController::class, 'storeSiswa']);
        Route::put('/siswa/{id}', [AdminController::class, 'updateSiswa']);
        Route::delete('/siswa/{id}', [AdminController::class, 'deleteSiswa']);

        Route::get('/kelas', [AdminController::class, 'indexKelas']);
        Route::post('/kelas', [AdminController::class, 'storeKelas']);
        Route::put('/kelas/{id}', [AdminController::class, 'updateKelas']);
        Route::delete('/kelas/{id}', [AdminController::class, 'deleteKelas']);
});

    // ===== GURU ROUTES =====
    Route::middleware(['auth:sanctum', 'guru'])->group(function () {
        Route::get('/guru/kelas', [GuruController::class, 'indexKelas']);
        Route::get('/guru/jadwal', [GuruController::class, 'indexJadwal']);
        Route::post('/guru/absen', [GuruController::class, 'absenSiswa']);
        Route::get('/guru/rekap', [GuruController::class, 'rekapAbsensiKelas']);
    });
});
