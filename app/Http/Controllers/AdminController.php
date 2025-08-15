<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminController extends Controller
{

    public function dashboard()
        {
            // Menghitung total guru dari tabel 'users' dengan role 'guru'
            $totalGuru = User::where('role', 'guru')->count();

            // Menghitung total siswa dari tabel 'siswa'
            $totalSiswa = Siswa::count();

            // Menghitung total kelas dari tabel 'kelas'
            $totalKelas = Kelas::count();
            
            // Data absensi hari ini (diisi dengan data dummy untuk contoh)
            $absensiHariIni = 0;

            return view('admin.dashboard', compact('totalGuru', 'totalSiswa', 'totalKelas', 'absensiHariIni'));
        }


    /* =============================
     *  GURU MANAGEMENT
     * ============================= */
    public function indexGuru()
    {
        $gurus = User::where('role', 'guru')->get();

        return response()->json([
            'status' => 'success',
            'data' => $gurus
        ], 200);

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); // default 10 per halaman

        $query = User::when($search, function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama', 'asc');

        $guru = $query->paginate($perPage);

        return response()->json([
            'data' => $guru->items(),
            'meta' => [
                'current_page' => $guru->currentPage(),
                'last_page' => $guru->lastPage(),
                'total' => $guru->total(),
                'per_page' => $guru->perPage(),
            ]
        ]);
    }

    public function storeGuru(Request $request)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $guru = User::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data guru berhasil dibuat',
            'data' => $guru
        ], 201);
    }

    public function updateGuru(Request $request, $id)
    {
        $guru = User::where('role', 'guru')->find($id);
        if (!$guru) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan'
            ], 404);
        }

        $rules = [
            'nama' => 'sometimes|required|string|max:255',
            'nip' => 'sometimes|required|string|unique:users,nip,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $dataUpdate = $request->only(['nama', 'nip', 'email']);
        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        $guru->update($dataUpdate);

        return response()->json([
            'status' => 'success',
            'message' => 'Data guru berhasil diperbarui',
            'data' => $guru
        ], 200);
    }

    public function destroyGuru($id)
    {
        $guru = User::where('role', 'guru')->find($id);
        if (!$guru) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan'
            ], 404);
        }

        $guru->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data guru berhasil dihapus'
        ], 200);
    }

    public function showGuru(Request $request, $id = null)
    {
        // Mulai query untuk guru
        $query = User::where('role', 'guru');

        // Jika ada ID, batasi ke ID tersebut
        if ($id) {
            $query->where('id', $id);
        }

        // Jika ada search (nama atau NIP)
        $search = $request->query('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $guru = $query->get();

        if ($guru->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Guru tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $guru
        ]);
    }

    public function generateQrAbsensi(Request $request)
    {
        $status = $request->input('status', 'datang'); // default datang
        $expiredAt = now()->addMinutes(10);

        $payload = [
            'status' => $status,
            'expired_at' => $expiredAt->timestamp
        ];

        $token = base64_encode(json_encode($payload));

        // Generate QR dalam bentuk PNG base64
        $qr = base64_encode(QrCode::format('png')->size(200)->generate($token));

        return response()->json([
            'status' => 'success',
            'qr_html' => "data:image/png;base64," . $qr,
            'token' => $token,
            'expired_at' => $expiredAt
        ]);
    }




    /* =============================
     *  SISWA MANAGEMENT
     * ============================= */
    public function indexSiswa()
    {
        $siswa = Siswa::all();
        return response()->json([
            'status' => 'success',
            'data' => $siswa
        ], 200);

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); // default 10 per halaman

        $query = Siswa::with('kelas') // load relasi kelas
            ->when($search, function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->orderBy('nama', 'asc');

        $siswa = $query->paginate($perPage);

        return response()->json([
            'data' => $siswa->items(),
            'meta' => [
                'current_page' => $siswa->currentPage(),
                'last_page' => $siswa->lastPage(),
                'total' => $siswa->total(),
                'per_page' => $siswa->perPage(),
            ]
        ]);
    }

    public function storeSiswa(Request $request)
    {
        $rules = [
            'nama' => 'required|string',
            'nisn' => 'required|string|unique:siswa,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // 1️⃣ Simpan siswa dulu (qr_code kosong sementara)
        $siswa = Siswa::create([
            'nama' => $request->nama,
            'nisn' => $request->nisn,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'kelas_id' => $request->kelas_id,
            'qr_code' => '', // isi sementara
            'qr_token' => '', // isi sementara
        ]);

        // 2️⃣ Generate QR Token dan QR Code
        $qrToken = Str::random(32);
        $qrCodePath = 'qrcodes/' . $request->nisn . '.png';
        $url = url('/siswa/' . $siswa->id);

        QrCode::format('png')->size(200)->generate($url, public_path($qrCodePath));

        // 3️⃣ Update siswa dengan QR info
        $siswa->update([
            'qr_code' => $qrCodePath,
            'qr_token' => $qrToken,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Siswa berhasil ditambahkan',
            'data' => $siswa
        ], 201);
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        $rules = [
            'nama' => 'sometimes|required|string',
            'nisn' => 'sometimes|required|string|unique:siswa,nisn,' . $id,
            'jenis_kelamin' => 'sometimes|required|in:L,P',
            'alamat' => 'sometimes|required|string',
            'kelas_id' => 'sometimes|required|exists:kelas,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $siswa->update($request->only(['nama', 'nisn', 'jenis_kelamin', 'alamat', 'kelas_id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Data siswa berhasil diperbarui',
            'data' => $siswa
        ], 200);
    }

    public function destroySiswa($id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        $siswa->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Siswa berhasil dihapus'
        ], 200);
    }

    public function showSiswa(Request $request, $id = null)
    {
        $query = Siswa::with('kelas');

        $query->when($id, function($q, $id) {
            $q->where('id', $id);
        });

        $query->when($request->query('search'), function($q, $search) {
            $q->where(function($subQ) use ($search) {
                $subQ->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        });

        $siswa = $query->get();

        if ($siswa->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $siswa
        ]);
    }




    /* =============================
     *  KELAS MANAGEMENT
     * ============================= */
    
    // GET: semua kelas
    public function indexKelas()
    {
        $kelas = Kelas::with('waliKelas', 'jadwalMapel')->get();
        return response()->json($kelas);
    }

    // POST: tambah kelas
    public function storeKelas(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string',
            'wali_kelas_id' => 'required|exists:users,id',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return response()->json([
            'message' => 'Kelas berhasil dibuat',
            'kelas' => $kelas
        ]);
    }

    // PUT: update kelas
    public function updateKelas(Request $request, $id)
    {
        $kelas = Kelas::find($id); // pastikan pakai find($id)
        if (!$kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_kelas' => 'required|string',
            'wali_kelas_id' => 'required|exists:users,id',
        ]);

        $kelas->update([
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return response()->json([
            'message' => 'Kelas berhasil diupdate',
            'kelas' => $kelas
        ]);
    }

    // DELETE: hapus kelas
    public function destroyKelas($id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }
        $kelas->delete();
        return response()->json(['message' => 'Kelas berhasil dihapus']);
    }

    // GET: tampil kelas tertentu beserta jadwal
    public function showKelas($id)
    {
        $kelas = Kelas::with('waliKelas', 'jadwalMapel.mataPelajaran', 'jadwalMapel.guru')->find($id);

        if (!$kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        return response()->json($kelas);
    }
}
