<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuruController extends Controller
{
    // Lihat kelas yang dia ajar
    public function indexKelas() {}

    // Lihat jadwal mengajar
    public function indexJadwal() {}

    // Absensi siswa (scan QR / manual)
    public function absenSiswa(Request $request) {}

    // Lihat rekap absensi kelasnya
    public function rekapAbsensiKelas() {}
}
