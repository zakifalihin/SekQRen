<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    // Admin bisa absen guru
    public function absenGuru(Request $request) {}

    // Guru absen diri sendiri
    public function absenGuruSendiri(Request $request) {}

    // Export laporan PDF / Excel
    public function exportAbsensiGuru($format) {}
    public function exportAbsensiSiswa($format) {}
}