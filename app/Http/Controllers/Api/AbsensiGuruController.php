<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiGuru;
use App\Models\JadwalMapelKelas;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AbsensiGuruController extends Controller
{
    /**
 * Memproses QR Code untuk absensi guru
 */
public function scanQr(Request $request)
{
    try {   
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode QR wajib diisi',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 2. Ambil guru yang login
        $guru = $request->user();
        if (!$guru) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 3. Decode QR
        $decoded = json_decode(base64_decode($request->kode), true);
        if (!$decoded || !isset($decoded['status'], $decoded['expired_at'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'QR Code tidak valid.'
            ], 400);
        }

        // 4. Validasi expired
        if (now()->timestamp > $decoded['expired_at']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'QR Code sudah kadaluarsa.'
            ], 400);
        }

        // 5. Siapkan data absensi
        $status  = $decoded['status']; // datang / pulang
        $tanggal = now()->toDateString();

        $absensiHariIni = AbsensiGuru::where('guru_id', $guru->id)
            ->where('tanggal', $tanggal)
            ->first();

        // 6. Logika absensi
        if ($status === 'datang') {
            if ($absensiHariIni && $absensiHariIni->jam_datang) {
                // ✅ tidak boleh absen datang 2x
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda sudah absen datang hari ini.'
                ], 409);
            }

            if ($absensiHariIni) {
                $absensiHariIni->update([
                    'jam_datang' => now()->format('H:i:s'),
                    'status'     => 'Hadir'
                ]);
            } else {
                $absensiHariIni = AbsensiGuru::create([
                    'guru_id'    => $guru->id,
                    'tanggal'    => $tanggal,
                    'status'     => 'Hadir',
                    'jam_datang' => now()->format('H:i:s')
                ]);
            }

        } elseif ($status === 'pulang') {
            if (!$absensiHariIni || !$absensiHariIni->jam_datang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda belum absen datang hari ini.'
                ], 400);
            }

            if ($absensiHariIni->jam_pulang) {
                // ✅ tidak boleh absen pulang 2x
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda sudah absen pulang hari ini.'
                ], 409);
            }

            $absensiHariIni->update(['jam_pulang' => now()->format('H:i:s')]);

        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Status absensi tidak valid pada QR Code.'
            ], 400);
        }

        // 7. Response sukses
        return response()->json([
            'status'  => 'success',
            'message' => 'Absensi ' . $status . ' berhasil direkam.',
            'data'    => $absensiHariIni->fresh()
        ]);

    } catch (\Exception $e) {
        // ✅ Catch semua error biar gak lempar ke Laravel default
        return response()->json([
            'status'  => 'error',
            'message' => 'Terjadi kesalahan internal',
            'hint'    => 'Coba ulangi atau hubungi admin',
            'error'   => $e->getMessage(), // Hapus di production biar aman
            'line'    => $e->getLine()
        ], 500);
    }
}

    /**
     * Memvalidasi token/kode QR yang di-scan oleh guru.
     * Route: GET /absen/validate
     */
    public function validateToken(Request $request)
    {
        // 1. Validasi input, pastikan ada parameter 'kode' di URL
        // Contoh URL: /api/absen/validate?kode=JADWAL-123-XYZ
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter "kode" tidak boleh kosong.',
            ], 422); // Unprocessable Entity
        }

        // 2. Cari jadwal berdasarkan kode QR
        $jadwal = JadwalMapelKelas::where('kode_qr', $request->kode)
                    ->with(['kelas:id,nama_kelas', 'mataPelajaran:id,nama_mapel', 'guru:id,nama'])
                    ->first();

        // 3. Jika jadwal tidak ditemukan
        if (!$jadwal) {
            return response()->json([
                'status' => 'error',
                'isValid' => false,
                'message' => 'Kode QR tidak valid atau jadwal tidak ditemukan.',
            ], 404); // Not Found
        }

        // 4. (Opsional) Cek apakah jadwal aktif saat ini
        $now = now('Asia/Makassar'); // Menggunakan zona waktu WITA
        $hariIni = $now->format('l');
        $jamSekarang = $now->format('H:i:s');
        
        $hariIndonesia = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];

        // Cek apakah hari dan jamnya sesuai
        if (($hariIndonesia[$hariIni] ?? '') !== $jadwal->hari) {
             return response()->json([
                'status' => 'error',
                'isValid' => false,
                'message' => 'Jadwal ini bukan untuk hari ini.',
            ], 400); // Bad Request
        }

        if ($jamSekarang < $jadwal->jam_mulai || $jamSekarang > $jadwal->jam_selesai) {
             return response()->json([
                'status' => 'error',
                'isValid' => false,
                'message' => 'Jadwal ini belum dimulai atau sudah berakhir.',
            ], 400); // Bad Request
        }

        // 5. Jika semua pengecekan lolos, kirim respons sukses
        return response()->json([
            'status' => 'success',
            'isValid' => true,
            'message' => 'Kode QR valid dan jadwal aktif.',
            'data' => [
                'kelas' => $jadwal->kelas->nama_kelas ?? '-',
                'mata_pelajaran' => $jadwal->mataPelajaran->nama_mapel ?? '-',
                'guru' => $jadwal->guru->nama ?? '-',
                'waktu' => substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5),
            ]
        ]);
    }


    public function hariIni()
    {
        $today = Carbon::now()->toDateString();

        // Ambil semua guru
        $allGuru = \App\Models\User::where('role', 'guru')->get();

        // Ambil absensi hari ini
        $absensiHariIni = AbsensiGuru::with('guru')
            ->whereDate('tanggal', $today)
            ->get()
            ->keyBy('guru_id'); // biar mudah dicocokkan

        // Siapkan data final
        $data = $allGuru->map(function ($guru) use ($absensiHariIni) {
            $absen = $absensiHariIni->get($guru->id);

            if (!$absen) {
                $status = 'Alpa';
            } elseif ($absen->jam_pulang) {
                $status = 'Pulang';
            } else {
                $status = 'Hadir';
            }

            return [
                'guru'       => $guru,
                'status'     => $status,
                'jam_datang' => $absen->jam_datang ?? null,
                'jam_pulang' => $absen->jam_pulang ?? null,
                'updated_at' => $absen->updated_at ?? null,
            ];
        });

        return view('admin.absensi.hari_ini', [
            'data'  => $data,
            'today' => $today
        ]);
    }

}
