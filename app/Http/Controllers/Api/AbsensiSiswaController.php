<?php

namespace App\Http\Controllers\Api; // <-- Namespace API yang Benar

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiSession; // Model Sesi Keamanan
use App\Models\Jadwal; // Mengganti JadwalMapelKelas ke Jadwal (Asumsi nama model)
use Illuminate\Support\Facades\DB; // Untuk transaksi database yang lebih aman
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AbsensiSiswaController extends Controller
{
    /**
     * API Task A3: Memproses scan QR Code Siswa dari aplikasi Flutter.
     * Endpoint: POST /api/absensi/scan
     */
    public function processScanSiswa(Request $request) // ⬅️ Nama method lebih spesifik
    {
        // Gunakan transaksi untuk menjamin integritas data (rollback jika ada error)
        DB::beginTransaction();

        try {
            // 1. VALIDASI INPUT
            $validated = $request->validate([
                'session_token' => 'required|string|size:32', // Tambahkan size untuk token (sesuai Task A2)
                'siswa_qr_token' => 'required|string', 
                'status' => 'required|in:Hadir,Terlambat,Izin,Sakit', 
                'keterangan' => 'nullable|string'
            ]);
            
            $token = $validated['session_token'];
            $siswaQr = $validated['siswa_qr_token'];
            $status = $validated['status'];
            $keterangan = $validated['keterangan'];
            $sekarang = Carbon::now('Asia/Makassar');

            // 2. VALIDASI TOKEN SESI (Keamanan & Konteks Absensi)
            // ⚠️ Perbaikan: Menggunakan 'session_token' sesuai field di DB (Asumsi dari Task A2)
            $session = AbsensiSession::where('session_token', $token) 
                ->where('status', 'active') // Cek status aktif, tidak perlu is_expired jika ada status
                ->with('jadwal') // Eager load relasi jadwal
                ->first();

            if (!$session || $session->jadwal == null) {
                // ⚠️ Jika sesi tidak ditemukan atau relasi jadwalnya putus
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi absensi tidak valid, sudah berakhir, atau data jadwal hilang.'
                ], 401);
            }
            
            // Ambil data Konteks dari Sesi yang Aman
            $jadwal = $session->jadwal; 
            $guruId = $session->guru_id;
            // ⚠️ Asumsi Jadwal memiliki field mapel_id dan kelas_id
            $mapelId = $jadwal->mapel_id; 
            $kelasId = $jadwal->kelas_id; 

            // 3. CARI SISWA BERDASARKAN QR TOKEN
            $siswa = Siswa::where('qr_token', $siswaQr)->first();

            if (!$siswa) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'QR Code Siswa tidak valid atau tidak terdaftar.'
                ], 404);
            }
            
            // ⚠️ Perbaikan: Cek apakah siswa yang discan adalah siswa dari kelas yang sedang diajar
            if ($siswa->kelas_id !== $kelasId) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => "Siswa {$siswa->nama} bukan dari Kelas {$jadwal->kelas->nama_kelas} yang diajar pada sesi ini."
                ], 403); // 403 Forbidden
            }

            // 4. CEK DUPLIKASI ABSENSI
            // Absen ganda untuk mapel yang sama oleh guru yang sama pada hari yang sama
            $sudahAbsen = AbsensiSiswa::where('siswa_id', $siswa->id)
                ->where('tanggal', $sekarang->toDateString())
                ->where('mapel_id', $mapelId)
                // ⚠️ Opsional: Bisa ditambahkan where('guru_id', $guruId) untuk akuntabilitas lebih
                ->exists();

            if ($sudahAbsen) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => "Siswa {$siswa->nama} sudah diabsen hari ini untuk mata pelajaran ini."
                ], 409); // 409 Conflict: Duplikasi
            }
            
            // 5. SIMPAN ABSENSI FINAL
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

            DB::commit(); // Commit transaksi jika semua berhasil

            // 6. RESPONSE SUKSES
            return response()->json([
                'status'  => 'success',
                'message' => "Absensi siswa {$siswa->nama} berhasil dicatat.",
                'siswa_nama' => $siswa->nama,
                'status_dicatat' => $status,
                'kelas' => $jadwal->kelas->nama_kelas // Kirim nama kelas untuk konfirmasi UI
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi input gagal.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Absensi Scan Error: ' . $e->getMessage()); 
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan internal pada server.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}