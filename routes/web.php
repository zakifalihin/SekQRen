<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruController;

// Redirect root ke halaman login admin
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// ====================
// AUTH ADMIN
// ====================
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// ====================
// ADMIN AREA (Hanya untuk yang login)
// ====================
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Kelola Guru
    Route::get('/guru',         [AdminController::class, 'indexGuru'])->name('guru.index');
    Route::post('/guru',        [AdminController::class, 'storeGuru'])->name('guru.store');
    Route::get('/guru/{id}',    [AdminController::class, 'showGuru'])->name('guru.show');
    Route::put('/guru/{id}',    [AdminController::class, 'updateGuru'])->name('admin.guru.update');
    Route::delete('/guru/{id}', [AdminController::class, 'destroyGuru'])->name('guru.destroy');

    // routes/web.php
    Route::get('/absensi/qr', [AdminController::class, 'generateQrAbsensi'])->name('absensi.qr');

    // Rute Siswa
    Route::get('/siswa', [AdminController::class, 'indexSiswa'])->name('siswa.index');
    Route::post('/siswa', [AdminController::class, 'storeSiswa'])->name('siswa.store');
    Route::put('/siswa/{id}', [AdminController::class, 'updateSiswa'])->name('siswa.update');
    Route::delete('/siswa/{id}', [AdminController::class, 'destroySiswa'])->name('siswa.destroy');
    Route::post('/siswa/import', [AdminController::class, 'importSiswa'])->name('siswa.import');
    Route::get('/siswa/export-template', [AdminController::class, 'exportTemplateSiswa'])->name('siswa.export-template');

    // Kelola Kelas
    Route::get('/kelas',         [AdminController::class, 'indexKelas'])->name('kelas.index');
    Route::post('/kelas',        [AdminController::class, 'storeKelas'])->name('kelas.store');
    Route::put('/kelas/{id}',    [AdminController::class, 'updateKelas'])->name('kelas.update');
    Route::delete('/kelas/{id}', [AdminController::class, 'destroyKelas'])->name('kelas.destroy');

    // Rute Manajemen Mata Pelajaran
    Route::get('/mapel', [AdminController::class, 'indexMapel'])->name('mapel.index');
    Route::post('/mapel', [AdminController::class, 'storeMapel'])->name('mapel.store');
    Route::put('/mapel/{id}', [AdminController::class, 'updateMapel'])->name('mapel.update');
    Route::delete('/mapel/{id}', [AdminController::class, 'destroyMapel'])->name('mapel.destroy');

    // Rute Manajemen Jadwal
    Route::get('/jadwal', [AdminController::class, 'indexJadwal'])->name('jadwal.index');
    Route::post('/jadwal', [AdminController::class, 'storeJadwal'])->name('jadwal.store');
    Route::put('/jadwal/{id}', [AdminController::class, 'updateJadwal'])->name('jadwal.update');
    Route::delete('/jadwal/{id}', [AdminController::class, 'destroyJadwal'])->name('jadwal.destroy');

});
