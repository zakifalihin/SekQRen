<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class AbsensiGuruController extends Controller
{
    public function __construct()
    {
    $this->middleware('auth:sanctum');
    }

    // debug helper (sementara)
    public function me()
    {
        return response()->json([
            'auth_id' => auth('sanctum')->id(),
            'user' => auth('sanctum')->user(),
            'bearer' => request()->bearerToken()
        ]);
    }

    public function scanQR(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        // Pastikan token Bearer diterima
        if (! $request->bearerToken()) {
            return response()->json(['status'=>'error','message'=>'Authorization header (Bearer) tidak ditemukan'], 401);
        }

        // Ambil id user yang login via sanctum
        $guruId = auth('sanctum')->id();
        if (!$guruId) {
            return response()->json(['status'=>'error','message'=>'Unauthenticated'], 401);
        }

        // Pastikan user ada
        $user = DB::table('users')->where('id', $guruId)->first();
        if (!$user) {
            return response()->json(['status'=>'error','message'=>'User tidak ditemukan'], 404);
        }

        // Jika kamu punya kolom role, cek role
        if (isset($user->role) && ! in_array($user->role, ['guru','kepala_sekolah'])) {
            return response()->json(['status'=>'error','message'=>'Hanya guru yang dapat menggunakan endpoint ini'], 403);
        }

        // Cek QR dinamis
        $qr = DB::table('qr_code_guru')
            ->where('token', $request->token)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if (!$qr) {
            return response()->json(['status'=>'error','message'=>'QR Code guru tidak valid atau sudah kadaluarsa'], 404);
        }

        // Cek duplikasi
        $exists = DB::table('absensi_guru')
            ->where('guru_id', $guruId)
            ->whereDate('tanggal', now()->toDateString())
            ->exists();

        if ($exists) {
            return response()->json(['status'=>'error','message'=>'Anda sudah melakukan absensi hari ini'], 409);
        }

        // Insert absensi dengan try/catch
        try {
            $id = DB::table('absensi_guru')->insertGetId([
                'guru_id' => $guruId,
                'tanggal' => now()->toDateString(),
                'status' => 'Hadir',
                'waktu_scan' => now()->toTimeString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $e) {
            // Log error agar mudah investigasi
            Log::error('AbsensiGuru insert error: ' . $e->getMessage(), [
                'guru_id' => $guruId,
                'token' => $request->token,
                'exception' => $e->getMessage()
            ]);

            return response()->json(['status'=>'error','message'=>'Gagal mencatat absensi (DB error)'], 500);
        }

        return response()->json(['status'=>'success','message'=>'Absensi guru berhasil dicatat','absensi_id'=>$id]);
    }
}
