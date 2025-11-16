<?php

namespace App\Http\Controllers\Api; // <-- Namespace API yang Benar

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiSession; // Model Sesi Keamanan
use App\Models\JadwalMapelKelas; // Model untuk konteks Jadwal
use Illuminate\Support\Facades\Log; // Untuk Error Logging
use Carbon\Carbon;

class AbsensiSiswaController extends Controller
{
    /**
     * API Task A3: Memproses scan QR Code Siswa dari aplikasi Flutter.
     * Menggunakan Token Sesi untuk validasi konteks dan Guru Penanggung Jawab.
     * * Endpoint: POST /api/absensi/scan
     */
    public function scanQR(Request $request)
    {
        try {
            // 1. VALIDASI INPUT
            $validated = $request->validate([
                'session_token' => 'required|string', // Token Sesi dari Task A2
                'siswa_qr_token' => 'required|string', // Token QR Siswa
                'status' => 'required|in:Hadir,Terlambat,Izin,Sakit', // Status kehadiran
                'keterangan' => 'nullable|string' // Keterangan opsional
            ]);
            
            $token = $validated['session_token'];
            $siswaQr = $validated['siswa_qr_token'];
            $status = $validated['status'];
            $keterangan = $validated['keterangan'];
            $sekarang = Carbon::now('Asia/Makassar');

            // 2. VALIDASI TOKEN SESI (Keamanan & Konteks Absensi)
            $session = AbsensiSession::where('token', $token)
                ->where('is_expired', 0)
                ->where('expires_at', '>', $sekarang)
                ->with('jadwal') 
                ->first();

            if (!$session) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi absensi tidak valid atau telah kedaluwarsa.'
                ], 401);
            }

            // Ambil data Konteks dari Sesi yang Aman
            $jadwal = $session->jadwal; 
            $guruId = $session->guru_id;
            $mapelId = $jadwal->mata_pelajaran_id; 
            $kelasId = $jadwal->kelas_id; 

            // 3. CARI SISWA BERDASARKAN QR TOKEN
            $siswa = Siswa::where('qr_token', $siswaQr)->first();

            if (!$siswa) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'QR Code Siswa tidak valid atau tidak terdaftar.'
                ], 404);
            }

            // 4. CEK DUPLIKASI ABSENSI (Absen ganda untuk mapel yang sama pada hari yang sama)
            $sudahAbsen = AbsensiSiswa::where('siswa_id', $siswa->id)
                ->where('tanggal', $sekarang->toDateString())
                ->where('mapel_id', $mapelId)
                ->exists();

            if ($sudahAbsen) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Siswa {$siswa->nama} sudah diabsen hari ini untuk mata pelajaran ini"
                ], 409); // 409 Conflict: Duplikasi
            }
            
            // 5. SIMPAN ABSENSI FINAL (Log Absensi)
            AbsensiSiswa::create([
                'siswa_id'    => $siswa->id,
                'kelas_id'    => $kelasId, 
                'mapel_id'    => $mapelId, 
                'guru_id'     => $guruId, // Kunci Akuntabilitas
                'tanggal'     => $sekarang->toDateString(),
                'status'      => $status, 
                'keterangan'  => $keterangan, 
                'waktu_scan'  => $sekarang->toTimeString(),
            ]);

            // 6. RESPONSE SUKSES
            return response()->json([
                'status'  => 'success',
                'message' => "Absensi siswa {$siswa->nama} berhasil dicatat.",
                'siswa_nama' => $siswa->nama,
                'status_dicatat' => $status
            ], 201); // 201 Created
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error dari validasi input (misal: status tidak valid)
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi input gagal.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            // Error umum (misal: masalah database, relasi)
            Log::error('API Absensi Scan Error: ' . $e->getMessage()); 
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan internal pada server.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    
    // Anda bisa tambahkan fungsi lain untuk Web Controller di sini,
    // atau biarkan Controller API ini fokus hanya pada layanan mobile.
}