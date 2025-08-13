<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    // Admin bisa absen guru
    public function absenGuru(Request $request) {}

    // Guru absen diri sendiri
    public function absenGuruSendiri(Request $request) {}

    // Export laporan PDF / Excel
    public function exportAbsensiGuru($format) {}
    public function exportAbsensiSiswa($format) {}

    public function scanQR(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        // Cari siswa berdasarkan token
        $siswa = DB::table('siswa')->where('qr_token', $request->token)->first();

        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak valid'
            ], 404);
        }

        // Catat absensi
        DB::table('absensi')->insert([
            'siswa_id'   => $siswa->id,
            'tanggal'    => now()->toDateString(),
            'waktu'      => now()->toTimeString(),
            'status'     => 'Hadir',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Absensi untuk {$siswa->nama} berhasil dicatat",
            'data'    => $siswa
        ]);
    }
}