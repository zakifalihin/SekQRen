<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiGuru;

class AbsensiGuruController extends Controller
{
    public function scanQr(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'guru_id' => 'required|integer'
        ]);

        // Decode token
        $decoded = json_decode(base64_decode($request->token), true);

        if (!$decoded || !isset($decoded['status'], $decoded['expired_at'])) {
            return response()->json(['status' => 'error', 'message' => 'Token tidak valid'], 400);
        }

        // Cek expired
        if (now()->timestamp > $decoded['expired_at']) {
            return response()->json(['status' => 'error', 'message' => 'QR Code sudah kadaluarsa'], 400);
        }

        $status = $decoded['status'];
        $tanggal = now()->toDateString();

        // Cek apakah guru sudah absen untuk status ini hari ini
        $cek = AbsensiGuru::where('guru_id', $request->guru_id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($status === 'datang') {
            if ($cek && $cek->jam_datang) {
                return response()->json(['status' => 'error', 'message' => 'Sudah absen datang hari ini'], 400);
            }

            if (!$cek) {
                AbsensiGuru::create([
                    'guru_id' => $request->guru_id,
                    'tanggal' => $tanggal,
                    'status' => 'hadir',
                    'jam_datang' => now()->format('H:i:s')
                ]);
            } else {
                $cek->update(['jam_datang' => now()->format('H:i:s')]);
            }
        } elseif ($status === 'pulang') {
            if (!$cek || !$cek->jam_datang) {
                return response()->json(['status' => 'error', 'message' => 'Belum absen datang'], 400);
            }
            if ($cek->jam_pulang) {
                return response()->json(['status' => 'error', 'message' => 'Sudah absen pulang hari ini'], 400);
            }
            $cek->update(['jam_pulang' => now()->format('H:i:s')]);
        }

        return response()->json(['status' => 'success', 'message' => 'Absensi berhasil']);
    }
}