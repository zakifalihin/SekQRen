<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\AbsensiSiswaController;
use App\Http\Controllers\Api\AbsensiGuruController;

// // Arahkan root ke halaman login admin
// Route::get('/', function () {
//     return redirect('/admin/login');
// });

// // Halaman-halaman admin (pakai Blade View)
// Route::view('/admin/login', 'auth.login')->name('admin.login');

// Route::prefix('admin')->group(function () {
//     Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
//     Route::view('/guru', 'admin.guru')->name('admin.guru');
//     Route::view('/siswa', 'admin.siswa')->name('admin.siswa');
//     Route::view('/kelas', 'admin.kelas')->name('admin.kelas');
//     Route::view('/absensi/generate', 'admin.generate-qr')->name('admin.generateqr');
// });



// // ========== PUBLIC ROUTES ==========
// Route::post('/login/admin', [AuthController::class, 'loginAdmin']);
// Route::post('/login/user', [AuthController::class, 'loginUser']);
// // Profile & Logout bisa diakses oleh semua yang sudah login (admin atau user)
//     Route::get('/profile', [AuthController::class, 'profile']);
//     Route::post('/logout', [AuthController::class, 'logout']);

// // ========== PROTECTED ROUTES ==========
// // Semua route di sini butuh authentication, entah admin atau user (guru/kepsek)
// Route::middleware('auth:sanctum')->group(function () {

//     // ===== ADMIN ROUTES =====
//     // Gunakan guard admin & middleware admin supaya hanya admin yang bisa akses
//     Route::prefix('admin')->middleware(['admin'])->group(function () {

//         // Guru
//         Route::get('/guru', [AdminController::class, 'indexGuru']);
//         Route::post('/guru', [AdminController::class, 'storeGuru']);
//         Route::put('/guru/{id}', [AdminController::class, 'updateGuru']);
//         Route::delete('/guru/{id}', [AdminController::class, 'destroyGuru']);
//         Route::get('/guru', [AdminController::class, 'showGuru']);
//         Route::get('/guru/{id}', [AdminController::class, 'showGuru']);
//         Route::post('/absensi/generate', [AdminController::class, 'generateQrAbsensi']);

//         // Siswa
//         Route::get('/siswa', [AdminController::class, 'indexSiswa']);
//         Route::post('/siswa', [AdminController::class, 'storeSiswa']);
//         Route::put('/siswa/{id}', [AdminController::class, 'updateSiswa']);
//         Route::delete('/siswa/{id}', [AdminController::class, 'destroySiswa']);
//         Route::get('/siswa', [AdminController::class, 'showSiswa']);
//         Route::get('/siswa/{id}', [AdminController::class, 'showSiswa']);

//         // Kelas
//         Route::get('/kelas', [AdminController::class, 'indexKelas']);
//         Route::post('/kelas', [AdminController::class, 'storeKelas']);
//         Route::put('/kelas/{id}', [AdminController::class, 'updateKelas']);
//         Route::delete('/kelas/{id}', [AdminController::class, 'destroyKelas']);
//         Route::get('/kelas/{id}/jadwal', [AdminController::class, 'showKelas']);
//     });

    // // ===== GURU ROUTES =====
    // // Gunakan middleware role guru untuk batasi akses ke guru saja
    // Route::middleware(['role:guru'])->group(function () {
    //     Route::get('/guru/kelas', [GuruController::class, 'indexKelas']);
    //     Route::get('/guru/jadwal', [GuruController::class, 'indexJadwal']);
    //     Route::post('/guru/absen', [GuruController::class, 'absenSiswa']);
    //     Route::get('/guru/rekap', [GuruController::class, 'rekapAbsensiKelas']);
    // });

    // // ========== GURU QR-CODE ==========
    // Route::post('/guru/absensi/scan', [AbsensiGuruController::class, 'scanQr']);
    // Route::post('/absensi/siswa/scan', [AbsensiSiswaController::class, 'scanQR']);


    // Kalau ada Kepala Sekolah juga, kamu bisa tambahkan group route seperti ini:
    // Route::middleware(['role:kepsek'])->group(function () {
    //     // route khusus kepsek
    // });

//});
