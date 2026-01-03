<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiSession; // Model untuk menyimpan token sesi (Task A2)
use App\Models\JadwalMapelKelas; // Sesuaikan dengan model jadwal kamu
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AbsensiSiswaController extends Controller
{
    /**
     * 🚀 TASK A2: Memulai sesi absensi dan generate Session Token.
     * Endpoint: POST /api/absensi/start
     * Dipanggil saat guru menekan tombol "LAKUKAN ABSENSI"
     */
    public function startAbsensiSession(Request $request)
    {
        // 1. Validasi Input dari Flutter
        // Pastikan key-nya 'id_jadwal' (sesuai jsonEncode di Flutter)
        $request->validate([
            'id_jadwal' => 'required|exists:jadwal_mapel_kelas,id',
        ]);

        try {
            $guru = $request->user(); // Ambil data guru dari Token Login

            // 2. (Opsional) Cek apakah sudah ada sesi aktif untuk jadwal ini?
            // Supaya kalau guru keluar-masuk menu, tokennya tidak berubah-ubah terus.
            $existingSession = AbsensiSession::where('jadwal_id', $request->id_jadwal)
                ->where('guru_id', $guru->id)
                ->where('status', 'active')
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($existingSession) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Melanjutkan sesi yang sudah ada.',
                    'data' => [
                        'session_token' => $existingSession->session_token
                    ]
                ], 200);
            }

            // 3. Generate Token Baru
            $token = Str::random(32); // String acak 32 karakter

            // 4. Simpan ke Database
            $session = AbsensiSession::create([
                // KIRI: Nama Kolom Database, KANAN: Data dari Request
                'jadwal_id'     => $request->id_jadwal, // ✅ Pastikan KIRI adalah 'jadwal_id'
                'guru_id'       => $guru->id,
                'session_token' => $token,
                'status'        => 'active',
                'expires_at'    => Carbon::now()->addHours(2), 
            ]);

            // 5. Kirim Token ke Flutter
            return response()->json([
                'status' => 'success',
                'message' => 'Sesi absensi dimulai.',
                'data' => [
                    'session_token' => $session->session_token
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Start Absensi Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🚀 TASK A3: Memproses scan QR Code Siswa.
     * Endpoint: POST /api/absensi/catat (atau /api/absensi/scan)
     * Dipanggil saat kamera Flutter berhasil mendeteksi QR Code siswa
     */
    
    public function catatKehadiran(Request $request) 
    {
        // 1. Validasi Input dari Flutter
        $request->validate([
            'session_token' => 'required|string',
            'id_siswa'      => 'required|exists:siswa,id',
            'id_jadwal'     => 'required|exists:jadwal_mapel_kelas,id', // Sesuai tabel jadwal kamu
            'status'        => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpha,Absen', 
        ]);

        try {
            $sekarang = \Carbon\Carbon::now();

            // 2. Validasi Token Sesi (Keamanan)
            // Pastikan sesi sesuai dengan jadwal yang dikirim
            $session = \App\Models\AbsensiSession::where('session_token', $request->session_token)
                ->where('jadwal_id', $request->id_jadwal) // Pastikan token milik jadwal ini
                ->where('status', 'active')
                ->where('expires_at', '>', $sekarang)
                ->first();

            if (!$session) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi absensi tidak valid atau sudah berakhir.'
                ], 401);
            }
            
            // 3. Cek Duplikasi (Agar tidak double absen)
            $sudahAbsen = \App\Models\AbsensiSiswa::where('siswa_id', $request->id_siswa)
                ->where('jadwal_mapel_kelas_id', $request->id_jadwal)
                ->where('tanggal', $sekarang->toDateString())
                ->exists();

            if ($sudahAbsen) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Siswa ini sudah diabsen sebelumnya."
                ], 409); // 409 Conflict
            }
            
            // 4. Simpan ke Database (Sesuai struktur tabel baru)
            $absen = new \App\Models\AbsensiSiswa();
            $absen->siswa_id = $request->id_siswa;
            $absen->jadwal_mapel_kelas_id = $request->id_jadwal; // ✅ Ini kolom baru yang benar
            $absen->tanggal = $sekarang->toDateString();
            $absen->waktu_absen = $sekarang->toTimeString();
            $absen->status = $request->status;
            $absen->save();

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil mencatat kehadiran.",
                'data'    => $absen
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencatat absensi: ' . $e->getMessage()
            ], 500);
        }
    }
}