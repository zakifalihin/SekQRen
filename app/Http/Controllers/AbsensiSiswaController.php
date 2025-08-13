<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;

class AbsensiSiswaController extends Controller
{
    public function scanQR(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'token'     => 'required|string',
            'mapel_id'  => 'required|integer',
            'kelas_id'  => 'required|integer'
        ]);

        // Cari siswa berdasarkan token QR statis di database
        $siswa = Siswa::where('qr_token', $validated['token'])->first();

        if (!$siswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'QR Code tidak valid'
            ], 404);
        }

        // Cek apakah siswa sudah absen di tanggal & mapel ini
        $sudahAbsen = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->where('tanggal', now()->toDateString())
            ->where('mapel_id', $validated['mapel_id'])
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Siswa sudah absen hari ini untuk mata pelajaran ini'
            ], 409);
        }

        // Simpan absensi
        AbsensiSiswa::create([
            'siswa_id'    => $siswa->id,
            'kelas_id'    => $validated['kelas_id'],
            'mapel_id'    => $validated['mapel_id'],
            'tanggal'     => now()->toDateString(),
            'status'      => 'Hadir',
            'waktu_scan'  => now()->toTimeString(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Absensi siswa {$siswa->nama} berhasil dicatat",
            'data'    => $siswa
        ]);
    }
}
