<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiGuru;

// class AbsensiGuruController extends Controller
// {
//     public function scanQr(Request $request)
//     {
//         $request->validate([
//             'token' => 'required',
//             'guru_id' => 'required|integer'
//         ]);

//         // Decode token
//         $decoded = json_decode(base64_decode($request->token), true);

//         if (!$decoded || !isset($decoded['status'], $decoded['expired_at'])) {
//             return response()->json(['status' => 'error', 'message' => 'Token tidak valid'], 400);
//         }

//         // Cek expired
//         if (now()->timestamp > $decoded['expired_at']) {
//             return response()->json(['status' => 'error', 'message' => 'QR Code sudah kadaluarsa'], 400);
//         }

//         $status = $decoded['status'];
//         $tanggal = now()->toDateString();

//         // Cek apakah guru sudah absen untuk status ini hari ini
//         $cek = AbsensiGuru::where('guru_id', $request->guru_id)
//             ->where('tanggal', $tanggal)
//             ->first();

//         if ($status === 'datang') {
//             if ($cek && $cek->jam_datang) {
//                 return response()->json(['status' => 'error', 'message' => 'Sudah absen datang hari ini'], 400);
//             }

//             if (!$cek) {
//                 AbsensiGuru::create([
//                     'guru_id' => $request->guru_id,
//                     'tanggal' => $tanggal,
//                     'status' => 'hadir',
//                     'jam_datang' => now()->format('H:i:s')
//                 ]);
//             } else {
//                 $cek->update(['jam_datang' => now()->format('H:i:s')]);
//             }
//         } elseif ($status === 'pulang') {
//             if (!$cek || !$cek->jam_datang) {
//                 return response()->json(['status' => 'error', 'message' => 'Belum absen datang'], 400);
//             }
//             if ($cek->jam_pulang) {
//                 return response()->json(['status' => 'error', 'message' => 'Sudah absen pulang hari ini'], 400);
//             }
//             $cek->update(['jam_pulang' => now()->format('H:i:s')]);
//         }

//         return response()->json(['status' => 'success', 'message' => 'Absensi berhasil']);
//     }
// }


class AbsensiGuruController extends Controller
{
    /**
     * Memproses QR Code absensi dari aplikasi Android.
     * Logika ini akan menyimpan data absensi ke database.
     */
    public function scanQr(Request $request)
    {
        // 1. Validasi Input dari Aplikasi Android
        $request->validate([
            'token' => 'required|string',
            'guru_id' => 'required|integer|exists:users,id' // Pastikan guru_id ada di tabel users
        ]);

        $decoded = json_decode(base64_decode($request->token), true);

        // 2. Validasi Token dan Waktu Kadaluarsa
        if (!$decoded || !isset($decoded['status'], $decoded['expired_at'])) {
            return response()->json(['status' => 'error', 'message' => 'Token tidak valid'], 400);
        }

        if (now()->timestamp > $decoded['expired_at']) {
            return response()->json(['status' => 'error', 'message' => 'QR Code sudah kadaluarsa'], 400);
        }

        $status = $decoded['status'];
        $tanggal = now()->toDateString();
        
        // 3. Cari entri absensi guru untuk hari ini
        $absensi = AbsensiGuru::where('guru_id', $request->guru_id)
            ->where('tanggal', $tanggal)
            ->first();

        // 4. Logika Absen Datang
        if ($status === 'datang') {
            if ($absensi) {
                // Jika sudah ada entri, cek apakah sudah absen datang
                if ($absensi->jam_datang) {
                    return response()->json(['status' => 'error', 'message' => 'Sudah absen datang hari ini'], 400);
                }
                // Jika belum absen datang, update jam datang
                $absensi->update([
                    'jam_datang' => now()->format('H:i:s'),
                    'status' => 'hadir' // Pastikan status di-update
                ]);
            } else {
                // Jika belum ada entri sama sekali, buat entri baru
                AbsensiGuru::create([
                    'guru_id' => $request->guru_id,
                    'tanggal' => $tanggal,
                    'status' => 'hadir',
                    'jam_datang' => now()->format('H:i:s')
                ]);
            }

        // 5. Logika Absen Pulang
        } elseif ($status === 'pulang') {
            if (!$absensi || !$absensi->jam_datang) {
                // Absen pulang hanya bisa jika sudah absen datang
                return response()->json(['status' => 'error', 'message' => 'Belum absen datang'], 400);
            }
            if ($absensi->jam_pulang) {
                return response()->json(['status' => 'error', 'message' => 'Sudah absen pulang hari ini'], 400);
            }
            // Update jam pulang
            $absensi->update([
                'jam_pulang' => now()->format('H:i:s')
            ]);

        } else {
            return response()->json(['status' => 'error', 'message' => 'Status absensi tidak valid'], 400);
        }

        return response()->json(['status' => 'success', 'message' => 'Absensi berhasil']);
    }
}