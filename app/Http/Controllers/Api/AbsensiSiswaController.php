<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiSession;
use App\Models\JadwalMapelKelas;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AbsensiSiswaController extends Controller
{
    
    /**
     * 🚀 TASK A2: Memulai sesi absensi
     */
    public function startAbsensiSession(Request $request)
    {
        $request->validate([
            'id_jadwal' => 'required|exists:jadwal_mapel_kelas,id',
        ]);

        try {
            $guru = $request->user();

            $existingSession = AbsensiSession::where('jadwal_id', $request->id_jadwal)
                ->where('guru_id', $guru->id)
                ->where('status', 'active')
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($existingSession) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Melanjutkan sesi yang sudah ada.',
                    'data' => ['session_token' => $existingSession->session_token]
                ], 200);
            }

            $token = \Illuminate\Support\Str::random(32);

            $session = AbsensiSession::create([
                'jadwal_id'     => $request->id_jadwal,
                'guru_id'       => $guru->id,
                'session_token' => $token,
                'status'        => 'active',
                'expires_at'    => Carbon::now()->addHours(2), 
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sesi absensi dimulai.',
                'data' => ['session_token' => $session->session_token]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Start Absensi Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Kesalahan server'], 500);
        }
    }

    /**
     * 🚀 TASK A3 & KONSEP 1: Catat kehadiran siswa + Update Jam Ajar Guru
     */
    public function catatKehadiran(Request $request) 
    {
        $request->validate([
            'session_token' => 'required|string',
            'id_siswa'      => 'required|exists:siswa,id',
            'id_jadwal'     => 'required|exists:jadwal_mapel_kelas,id',
            'status'        => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpha,Absen', 
        ]);

        try {
            $sekarang = Carbon::now();
            $hariIni = $sekarang->toDateString();
            $jamSekarang = $sekarang->format('H:i:s');

            // 1. Validasi Token Sesi
            $session = AbsensiSession::where('session_token', $request->session_token)
                ->where('jadwal_id', $request->id_jadwal)
                ->where('status', 'active')
                ->where('expires_at', '>', $sekarang)
                ->first();

            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Sesi tidak valid.'], 401);
            }

            // 2. VALIDASI RANGE WAKTU (Konsep 1)
            $jadwal = JadwalMapelKelas::find($request->id_jadwal);
            if ($jamSekarang < $jadwal->jam_mulai || $jamSekarang > $jadwal->jam_selesai) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Gagal! Jadwal Anda: {$jadwal->jam_mulai} - {$jadwal->jam_selesai}. Sekarang: {$jamSekarang}."
                ], 403);
            }
            
            // 3. Cek Duplikasi Absen Siswa
            $sudahAbsen = AbsensiSiswa::where('siswa_id', $request->id_siswa)
                ->where('jadwal_mapel_kelas_id', $request->id_jadwal)
                ->where('tanggal', $hariIni)
                ->exists();

            if ($sudahAbsen) {
                return response()->json(['status' => 'error', 'message' => "Siswa sudah diabsen."], 409);
            }
            
            // 4. Simpan Absensi Siswa
            $absen = new AbsensiSiswa();
            $absen->siswa_id = $request->id_siswa;
            $absen->jadwal_mapel_kelas_id = $request->id_jadwal;
            $absen->tanggal = $hariIni;
            $absen->waktu_absen = $sekarang->toTimeString();
            $absen->status = $request->status;
            $absen->save();

            // 5. LOGIKA UPDATE TOTAL JAM AJAR GURU
            // Cek apakah ini siswa PERTAMA yang berhasil di-absen pada jadwal ini hari ini
            $countSiswa = AbsensiSiswa::where('jadwal_mapel_kelas_id', $request->id_jadwal)
                ->where('tanggal', $hariIni)
                ->count();

            if ($countSiswa === 1) {
                // Hitung durasi jam pelajaran
                $mulai = Carbon::parse($jadwal->jam_mulai);
                $selesai = Carbon::parse($jadwal->jam_selesai);
                
                // Menggunakan diffInMinutes dibagi 60 agar presisi untuk decimal(4,2)
                $durasiJam = $mulai->diffInMinutes($selesai) / 60;
                
                // Minimal 1 jam jika durasi sangat singkat
                if ($durasiJam <= 0) $durasiJam = 1;

                // Cari record absensi guru hari ini
                $laporanGuru = AbsensiGuru::where('guru_id', $session->guru_id)
                    ->where('tanggal', $hariIni)
                    ->first();

                if ($laporanGuru) {
                    $laporanGuru->increment('total_jam_ajar', $durasiJam);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Kehadiran dicatat. Total jam ajar diperbarui.",
                'data'    => $absen
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 🚀 UPDATE STATUS SISWA: Digunakan untuk mengedit status yang sudah tercatat
     */
    public function updateStatusSiswa(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Hadir,Izin,Alpha,Sakit,Terlambat',
        ]);

        try {
            // Cari berdasarkan ID primer tabel absensi_siswa
            $absensi = AbsensiSiswa::find($id);

            if (!$absensi) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            $absensi->status = $request->status;
            $absensi->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil memperbarui status ke ' . $request->status
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    // public function startAbsensiSession(Request $request)
    // {
    //     // 1. Validasi Input dari Flutter
    //     // Pastikan key-nya 'id_jadwal' (sesuai jsonEncode di Flutter)
    //     $request->validate([
    //         'id_jadwal' => 'required|exists:jadwal_mapel_kelas,id',
    //     ]);

    //     try {
    //         $guru = $request->user(); // Ambil data guru dari Token Login

    //         // 2. (Opsional) Cek apakah sudah ada sesi aktif untuk jadwal ini?
    //         // Supaya kalau guru keluar-masuk menu, tokennya tidak berubah-ubah terus.
    //         $existingSession = AbsensiSession::where('jadwal_id', $request->id_jadwal)
    //             ->where('guru_id', $guru->id)
    //             ->where('status', 'active')
    //             ->where('expires_at', '>', Carbon::now())
    //             ->first();

    //         if ($existingSession) {
    //             return response()->json([
    //                 'status' => 'success',
    //                 'message' => 'Melanjutkan sesi yang sudah ada.',
    //                 'data' => [
    //                     'session_token' => $existingSession->session_token
    //                 ]
    //             ], 200);
    //         }

    //         // 3. Generate Token Baru
    //         $token = Str::random(32); // String acak 32 karakter

    //         // 4. Simpan ke Database
    //         $session = AbsensiSession::create([
    //             // KIRI: Nama Kolom Database, KANAN: Data dari Request
    //             'jadwal_id'     => $request->id_jadwal, // ✅ Pastikan KIRI adalah 'jadwal_id'
    //             'guru_id'       => $guru->id,
    //             'session_token' => $token,
    //             'status'        => 'active',
    //             'expires_at'    => Carbon::now()->addHours(2), 
    //         ]);

    //         // 5. Kirim Token ke Flutter
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Sesi absensi dimulai.',
    //             'data' => [
    //                 'session_token' => $session->session_token
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Start Absensi Error: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // /**
    //  * 🚀 TASK A3: Memproses scan QR Code Siswa.
    //  * Endpoint: POST /api/absensi/catat (atau /api/absensi/scan)
    //  * Dipanggil saat kamera Flutter berhasil mendeteksi QR Code siswa
    //  */
    
    // public function catatKehadiran(Request $request) 
    // {
    //     // 1. Validasi Input dari Flutter
    //     $request->validate([
    //         'session_token' => 'required|string',
    //         'id_siswa'      => 'required|exists:siswa,id',
    //         'id_jadwal'     => 'required|exists:jadwal_mapel_kelas,id', // Sesuai tabel jadwal kamu
    //         'status'        => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpha,Absen', 
    //     ]);

    //     try {
    //         $sekarang = \Carbon\Carbon::now();

    //         // 2. Validasi Token Sesi (Keamanan)
    //         // Pastikan sesi sesuai dengan jadwal yang dikirim
    //         $session = \App\Models\AbsensiSession::where('session_token', $request->session_token)
    //             ->where('jadwal_id', $request->id_jadwal) // Pastikan token milik jadwal ini
    //             ->where('status', 'active')
    //             ->where('expires_at', '>', $sekarang)
    //             ->first();

    //         if (!$session) {
    //             return response()->json([
    //                 'status'  => 'error',
    //                 'message' => 'Sesi absensi tidak valid atau sudah berakhir.'
    //             ], 401);
    //         }
            
    //         // 3. Cek Duplikasi (Agar tidak double absen)
    //         $sudahAbsen = \App\Models\AbsensiSiswa::where('siswa_id', $request->id_siswa)
    //             ->where('jadwal_mapel_kelas_id', $request->id_jadwal)
    //             ->where('tanggal', $sekarang->toDateString())
    //             ->exists();

    //         if ($sudahAbsen) {
    //             return response()->json([
    //                 'status'  => 'error',
    //                 'message' => "Siswa ini sudah diabsen sebelumnya."
    //             ], 409); // 409 Conflict
    //         }
            
    //         // 4. Simpan ke Database (Sesuai struktur tabel baru)
    //         $absen = new \App\Models\AbsensiSiswa();
    //         $absen->siswa_id = $request->id_siswa;
    //         $absen->jadwal_mapel_kelas_id = $request->id_jadwal; // ✅ Ini kolom baru yang benar
    //         $absen->tanggal = $sekarang->toDateString();
    //         $absen->waktu_absen = $sekarang->toTimeString();
    //         $absen->status = $request->status;
    //         $absen->save();

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => "Berhasil mencatat kehadiran.",
    //             'data'    => $absen
    //         ], 200);
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal mencatat absensi: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}