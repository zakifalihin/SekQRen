<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruController;

// Halaman login admin
Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login'); // form login
Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.login.post'); // proses login


Route::view('/admin/login', 'auth.login')->name('admin.login');

// Admin hanya bisa diakses jika login
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Guru
    Route::get('/guru', [AdminController::class, 'indexGuru'])->name('admin.guru.index');
    Route::post('/guru', [AdminController::class, 'storeGuru'])->name('admin.guru.store');
    Route::put('/guru/{id}', [AdminController::class, 'updateGuru'])->name('admin.guru.update');
    Route::delete('/guru/{id}', [AdminController::class, 'destroyGuru'])->name('admin.guru.destroy');

    // Siswa
    Route::get('/siswa', [AdminController::class, 'indexSiswa'])->name('admin.siswa.index');
    Route::post('/siswa', [AdminController::class, 'storeSiswa'])->name('admin.siswa.store');
    Route::put('/siswa/{id}', [AdminController::class, 'updateSiswa'])->name('admin.siswa.update');
    Route::delete('/siswa/{id}', [AdminController::class, 'destroySiswa'])->name('admin.siswa.destroy');

    // Kelas
    Route::get('/kelas', [AdminController::class, 'indexKelas'])->name('admin.kelas.index');
    Route::post('/kelas', [AdminController::class, 'storeKelas'])->name('admin.kelas.store');
    Route::put('/kelas/{id}', [AdminController::class, 'updateKelas'])->name('admin.kelas.update');
    Route::delete('/kelas/{id}', [AdminController::class, 'destroyKelas'])->name('admin.kelas.destroy');
});

// logout admin
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');