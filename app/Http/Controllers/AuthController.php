<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// class AuthController extends Controller
// {
//     /**
//      * Login Admin
//      */
//     public function loginAdmin(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'username' => 'required',
//             'password' => 'required'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
//         }

//         $admin = Admin::where('username', $request->username)->first();

//         if (!$admin || !Hash::check($request->password, $admin->password)) {
//             return response()->json(['status' => 'error', 'message' => 'Username atau password salah'], 401);
//         }

//         $token = $admin->createToken('admin-token')->plainTextToken;

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Login berhasil sebagai admin',
//             'token' => $token,
//             'data' => $admin
//         ]);
//     }

//     /**
//      * Login User (Guru / Kepala Sekolah)
//      */
//     public function loginUser(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'nip' => 'required',
//             'password' => 'required'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
//         }

//         $user = User::where('nip', $request->nip)->first();

//         if (!$user || !Hash::check($request->password, $user->password)) {
//             return response()->json(['status' => 'error', 'message' => 'NIP atau password salah'], 401);
//         }

//         $token = $user->createToken('user-token')->plainTextToken;

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Login berhasil sebagai ' . $user->role,
//             'token' => $token,
//             'data' => $user
//         ]);
//     }

//     /**
//      * Ambil data profile yang sedang login
//      */
//     public function profile(Request $request)
//     {
//         return response()->json([
//             'status' => 'success',
//             'data' => $request->user()
//         ]);
//     }

//     /**
//      * Logout
//      */
//     public function logout(Request $request)
//     {
//         $request->user()->tokens()->delete();
//         return response()->json(['status' => 'success', 'message' => 'Logout berhasil']);
//     }
// }


class AuthController extends Controller
{
    /**
     * Tampilkan halaman login admin
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Pastikan file ini ada di resources/views/auth/login.blade.php
    }

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Auth dengan guard admin
        if (Auth::guard('admin')->attempt([
            'username' => $request->username,
            'password' => $request->password
        ])) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah'
        ])->withInput();
    }
    /**
     * Logout admin
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}