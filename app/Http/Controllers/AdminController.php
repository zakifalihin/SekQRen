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
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiswaExportTemplate;

class AdminController extends Controller
{

    /* =============================
    *  DASHBOARD (WEB VERSION)
    * ============================= */
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
    *  GURU MANAGEMENT (WEB VERSION)
    * ============================= */


    public function indexGuru(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); // default 10 per halaman

        $query = User::where('role', 'guru')
            ->when($search, function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama', 'asc');

        $guru = $query->paginate($perPage);
        $totalGuru = User::where('role', 'guru')->count();

        return view('admin.guru.index', compact('guru', 'totalGuru', 'search'));
    }

    public function storeGuru(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru'
        ]);

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil dibuat');
    }

    public function updateGuru(Request $request, $id) // Ubah 'updateGuru' menjadi 'update'
    {
        $guru = User::where('role', 'guru')->findOrFail($id);

        $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'nip' => 'sometimes|required|string|unique:users,nip,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        $dataUpdate = $request->only(['nama', 'nip', 'email']);
        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        $guru->update($dataUpdate);

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil diperbarui');
    }

    public function destroyGuru($id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);
        $guru->delete();

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus');
    }

    public function showGuru(Request $request, $id = null)
    {
        $query = User::where('role', 'guru');

        if ($id) {
            $query->where('id', $id);
        }

        $search = $request->query('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $guru = $query->get();

        return view('guru.show', compact('guru'));
    }


        public function generateQrAbsensi(Request $request)
    {
        // Dapatkan status dari request, defaultnya 'datang'
        $status = $request->input('status', 'datang');
        
        // Dapatkan durasi dari request, defaultnya 10 menit
        $duration = (int) $request->input('duration', 5);
        
        // Pastikan durasi tidak nol atau negatif
        if ($duration <= 0) {
            $duration = 10;
        }

        // Buat objek Carbon untuk waktu kedaluwarsa
        $expiredAt = Carbon::now()->addMinutes($duration);

        // Buat payload untuk QR code
        $payload = [
            'status' => $status,
            'expired_at' => $expiredAt->timestamp
        ];
        $token = base64_encode(json_encode($payload));

        // Generate QR code
        $qr = QrCode::format('png')->size(200)->generate($token);
        $qr_html = "data:image/png;base64," . base64_encode($qr);

        // Kirim data ke view
        return view('admin.guru.qr', compact('qr_html', 'token', 'expiredAt', 'status'));
    }







    /* ==================================
     * SISWA MANAGEMENT (WEB VERSION)
     * ================================== */

    public function indexSiswa(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = Siswa::with('kelas')
            ->when($search, function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->orderBy('nama', 'asc');

        $siswa = $query->paginate($perPage);
        $totalSiswa = Siswa::count();
        $kelas = Kelas::all();

        return view('admin.siswa.index', compact('siswa', 'totalSiswa', 'kelas'));
    }

    public function storeSiswa(Request $request)
    {
        $rules = [
            'nama' => 'required|string',
            'nisn' => 'required|string|unique:siswa,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'kelas_id' => 'required|exists:kelas,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $qrToken = Str::random(32);
        $qrCodeData = json_encode(['nisn' => $request->nisn, 'token' => $qrToken]);
        
        $filename = 'qrcodes/' . $request->nisn . '.png';
        // SIMPAN KE FOLDER STORAGE
        Storage::disk('public')->put($filename, QrCode::format('png')->size(200)->generate($qrCodeData));
        
        $siswa = Siswa::create([
            'nama' => $request->nama,
            'nisn' => $request->nisn,
            'jenis_kelamin' => $request->jenis_kelamin,
            'agama' => $request->agama,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'kelas_id' => $request->kelas_id,
            'qr_code' => $filename, // Simpan path storage
            'qr_token' => $qrToken,
        ]);
        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan.');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'nisn' => ['required', 'string', 'max:255', Rule::unique('siswa')->ignore($siswa)],
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswaData = $request->only([
            'nama', 
            'nisn', 
            'jenis_kelamin', 
            'agama', 
            'nomor_telepon', 
            'alamat', 
            'kelas_id'
        ]);

        // Perbaikan: Logika untuk membuat ulang QR Code jika NISN berubah
        if ($request->nisn !== $siswa->nisn) {
            $qrToken = Str::random(32);
            $qrCodeData = json_encode(['nisn' => $request->nisn, 'token' => $qrToken, 'kelas_id' => $request->kelas_id]);
            
            // Hapus file QR code lama
            if (Storage::disk('public')->exists($siswa->qr_code)) {
                Storage::disk('public')->delete($siswa->qr_code);
            }

            // SIMPAN KE FOLDER STORAGE
            $filename = 'qrcodes/' . $request->nisn . '.png';
            Storage::disk('public')->put($filename, QrCode::format('png')->size(200)->generate($qrCodeData));
            
            $siswaData['qr_code'] = $filename;
            $siswaData['qr_token'] = $qrToken;
        }

        $siswa->update($siswaData);

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroySiswa($id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan');
        }

        $siswa->delete();

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil dihapus!');
    }


    public function importSiswa(Request $request)
    {
        // Validasi file yang diunggah
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            // Proses file menggunakan package Laravel Excel
            Excel::import(new SiswaImport, $request->file('file'));

            return redirect()->back()->with('success', 'Data siswa berhasil diimpor!');
        } catch (\Exception $e) {
            // Jika validasi gagal, tangkap error dan tampilkan pesan
            return redirect()->back()->with('error', 'Gagal mengimpor data siswa. Pesan: ' . $e->getMessage());
        }
    }

    public function exportTemplateSiswa()
    {
        return Excel::download(new SiswaExportTemplate, 'template-siswa.xlsx');
    }











//     /* =============================
//      *  KELAS MANAGEMENT
//      * ============================= */
    
//     // GET: semua kelas
//     public function indexKelas()
//     {
//         $kelas = Kelas::with('waliKelas', 'jadwalMapel')->get();
//         return response()->json($kelas);
//     }

//     // POST: tambah kelas
//     public function storeKelas(Request $request)
//     {
//         $request->validate([
//             'nama_kelas' => 'required|string',
//             'wali_kelas_id' => 'required|exists:users,id',
//         ]);

//         $kelas = Kelas::create([
//             'nama_kelas' => $request->nama_kelas,
//             'wali_kelas_id' => $request->wali_kelas_id,
//         ]);

//         return response()->json([
//             'message' => 'Kelas berhasil dibuat',
//             'kelas' => $kelas
//         ]);
//     }

//     // PUT: update kelas
//     public function updateKelas(Request $request, $id)
//     {
//         $kelas = Kelas::find($id); // pastikan pakai find($id)
//         if (!$kelas) {
//             return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
//         }

//         $request->validate([
//             'nama_kelas' => 'required|string',
//             'wali_kelas_id' => 'required|exists:users,id',
//         ]);

//         $kelas->update([
//             'nama_kelas' => $request->nama_kelas,
//             'wali_kelas_id' => $request->wali_kelas_id,
//         ]);

//         return response()->json([
//             'message' => 'Kelas berhasil diupdate',
//             'kelas' => $kelas
//         ]);
//     }

//     // DELETE: hapus kelas
//     public function destroyKelas($id)
//     {
//         $kelas = Kelas::find($id);
//         if (!$kelas) {
//             return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
//         }
//         $kelas->delete();
//         return response()->json(['message' => 'Kelas berhasil dihapus']);
//     }

//     // GET: tampil kelas tertentu beserta jadwal
//     public function showKelas($id)
//     {
//         $kelas = Kelas::with('waliKelas', 'jadwalMapel.mataPelajaran', 'jadwalMapel.guru')->find($id);

//         if (!$kelas) {
//             return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
//         }

//         return response()->json($kelas);
//     }
// }


/* =============================
     * KELAS MANAGEMENT (WEB VERSION)
     * ============================= */
    public function indexKelas()
    {
        // Mengembalikan view dengan data yang diperlukan
        $kelas = Kelas::with('waliKelas', 'jadwalMapel')->get();
        $waliKelasOptions = User::where('role', 'guru')->get();

        return view('admin.kelas.index', compact('kelas', 'waliKelasOptions'));
    }

    public function storeKelas(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string',
            'wali_kelas_id' => 'required|exists:users,id',
        ]);

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dibuat!');
    }

    public function updateKelas(Request $request, $id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        $request->validate([
            'nama_kelas' => ['required', 'string', Rule::unique('kelas')->ignore($kelas->id)],
            'wali_kelas_id' => 'required|exists:users,id',
        ]);

        $kelas->update([
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diupdate!');
    }

    public function destroyKelas($id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        $kelas->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus!');
    }
}