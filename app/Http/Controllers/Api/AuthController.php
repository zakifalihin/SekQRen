<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// class AuthController extends Controller
// {
//     public function login(Request $request)
//     {
//         // Validasi input
//         $request->validate([
//             'nip' => 'required',
//             'password' => 'required'
//         ]);

//         // Cari guru berdasarkan NIP
//         $user = User::where('nip', $request->nip)->first();

//         // Cek apakah guru ada dan password benar
//         if (!$user || !Hash::check($request->password, $user->password)) {
//             return response()->json(['message' => 'Login gagal, NIP atau Password salah'], 401);
//         }

//         // Buat token Sanctum
//         $token = $user->createToken('auth_token')->plainTextToken;

//         // Kirim respon JSON
//         return response()->json([
//             'message' => 'Login berhasil',
//             'token' => $token,
//             'user' => $user
//         ]);
//     }
// }


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'success',
                'user' => Auth::user(),
                'token' => Auth::user()->createToken('API Token')->plainTextToken
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Login gagal'], 401);
    }
}