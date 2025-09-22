<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Logout Guru
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Logout berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Logout gagal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
