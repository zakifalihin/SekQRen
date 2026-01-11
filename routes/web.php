<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\AbsensiGuruController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\RekapAbsensiController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


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

    // Absensi QRcode admin
    Route::get('/absensi/qr', [AdminController::class, 'generateQrAbsensi'])->name('absensi.qr');
    // QRcode Siswa
    Route::get('/generate-qr/{data}', function ($data) {
        return response(QrCode::format('png')->size(200)->margin(1)->generate($data))
                ->header('Content-Type', 'image/png');
    })->name('qr.generate');


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


    // Daftar absensi hari ini
    Route::get('/absensi/hari-ini', [AbsensiGuruController::class, 'hariIni'])->name('absensi.hariini');

    //Daftar Layout Operasional Absensi
    Route::get('absensi/guru', [RekapAbsensiController::class, 'guruIndex'])->name('absensi.guru'); // Monitor absensi guru
    Route::get('absensi/siswa', [RekapAbsensiController::class, 'siswaIndex'])->name('absensi.siswa'); // Status absensi siswa real-time
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');


    /** 
     * ================
     * Laporan Absensi Guru
     * ================
     * Tidak pakai API — semuanya lewat web + guard admin
     */
    Route::get('/laporan/guru/data', [RekapAbsensiController::class, 'getLaporanGuru'])->name('laporan.guru.data');
    Route::get('/laporan/guru/export', [RekapAbsensiController::class, 'exportLaporanGuru'])->name('laporan.guru.export');

    /**
     * ==============================
     * Laporan Absensi Siswa (WEB)
     * ==============================
     */
    Route::get('/laporan/siswa/data', [RekapAbsensiController::class, 'getAbsensiSiswaWeb'])->name('laporan.siswa.data');
    Route::get('/laporan/siswa/export', [RekapAbsensiController::class, 'exportAbsensiSiswaWeb'])->name('laporan.siswa.export');
});