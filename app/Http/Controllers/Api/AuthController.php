<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login Guru
     */
    public function login(Request $request)
    {
        $request->validate([
            'nip'      => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('nip', $request->nip)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'NIP atau password salah'
            ], 401);
        }

        try {
            // Hapus token lama sebelum buat baru (biar 1 device = 1 token)
            $user->tokens()->delete();

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'user'   => [
                    'id'    => $user->id,
                    'nama'  => $user->nama,
                    'nip'   => $user->nip,
                    'role'  => $user->role,
                ],
                'token'  => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token gagal dibuat',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // --- LOGOUT GURU ---
    public function logout(Request $request)
    {
        try {
            // Menghapus token yang sedang digunakan saat ini
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil keluar akun'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal logout: ' . $e->getMessage()
            ], 500);
        }
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            // 'confirmed' mewajibkan adanya field 'new_password_confirmation' dari Flutter
            'new_password' => 'required|min:6|confirmed', 
        ]);

        // Mengambil user yang sedang login via Sanctum
        $user = $request->user(); 

        // 1. Cek apakah password lama sesuai dengan yang ada di database
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password lama tidak sesuai.'
            ], 401);
        }

        // 2. Hash password baru dan masukkan ke objek user
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diperbarui.'
        ], 200);
    }
}
