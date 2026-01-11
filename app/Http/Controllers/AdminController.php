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
use App\Imports\SiswaImport;
use App\Models\MataPelajaran;
use App\Models\JadwalMapelKelas;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiswaExportTemplate;
use App\Models\AbsensiGuru;

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
            
            $absensiHariIni = AbsensiGuru::whereDate('tanggal', now()->toDateString())->count();

            // Ambil 10 aktivitas absensi terakhir (terbaru dulu), eager load guru
            $aktivitas = AbsensiGuru::with('guru')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            return view('admin.dashboard', compact('totalGuru', 'totalSiswa', 'totalKelas', 'absensiHariIni', 'aktivitas'));
        }


    /* =============================
    *  GURU MANAGEMENT (WEB VERSION)
    * ============================= */

    public function indexGuru(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); // Tetap mempertahankan per_page

        $query = User::where('role', 'guru')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama', 'asc');

        // !!! PERUBAHAN UTAMA: Menggunakan simplePaginate untuk Prev/Next saja
        $guru = $query->simplePaginate($perPage); 
        
        // appends tetap penting
        $guru->appends($request->only('search', 'per_page'));

        $totalGuru = User::where('role', 'guru')->count();

        // Jika request AJAX (untuk live search + pagination)
        if ($request->ajax()) {
            return view('admin.guru.index', compact('guru', 'totalGuru', 'search'))->render();
        }

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
        // Cari user dengan role guru, jika tidak ada akan return 404
        $guru = User::where('role', 'guru')->findOrFail($id);
        
        // Proses hapus data
        $guru->delete();

        // Redirect kembali ke halaman index guru dengan pesan sukses
        // Pastikan nama route sesuai (admin.guru.index)
        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil dihapus secara permanen');
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
        // 1. Validasi input
        $request->validate([
            'status'   => 'nullable|in:datang,pulang',
            'duration' => 'nullable|integer|min:1|max:60', // durasi 1–60 menit
        ]);

        // 2. Ambil status (default: datang) & durasi (default: 5 menit)
        $status   = $request->input('status', 'datang');
        $duration = (int) $request->input('duration', 5);

        // 3. Hitung expired time
        $expiredAt = Carbon::now()->addMinutes($duration);

        // 4. Buat payload untuk QR Code
        $payload = [
            'status'     => $status,
            'expired_at' => $expiredAt->timestamp,
        ];

        $token = base64_encode(json_encode($payload));

        // 5. Generate QR Code PNG → base64
        $qr = QrCode::format('png')->size(200)->generate($token);
        $qr_html = "data:image/png;base64," . base64_encode($qr);

        // 6. Kirim ke view (pastikan file ada di: resources/views/admin/guru/qr.blade.php)
        return view('admin.guru.qr', [
            'qr_html'   => $qr_html,
            'token'     => $token,
            'expiredAt' => $expiredAt,
            'status'    => $status,
            'duration'  => $duration
        ]);
    }




    /* ==================================
     * SISWA MANAGEMENT (WEB VERSION)
     * ================================== */

    public function indexSiswa(Request $request)
{
    $search = $request->input('search');
    $perPage = $request->input('per_page', 50);
    $kelasId = $request->input('kelas_id');

    $query = Siswa::with('kelas')
        ->when($search, function ($q) use ($search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama', 'like', "%{$search}%") 
            ->orWhere('nisn', 'like', "%{$search}%");
            });
        })
        ->when($kelasId, function ($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })
        ->orderBy('nama', 'asc');

    $siswa = $query->paginate($perPage);
    $siswa->appends($request->only('search', 'kelas_id', 'per_page'));

    if ($request->ajax()) {
        return view('admin.siswa.partials.siswa_table', compact('siswa'))->render();
    }

    $totalSiswa = Siswa::count();
    $kelas = Kelas::all();

    return view('admin.siswa.index', compact('siswa', 'totalSiswa', 'kelas', 'kelasId'));
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




    /* =======================================
     * MATA PELAJARAN MANAGEMENT (WEB VERSION)
     * ======================================= */
    public function indexMapel()
    {
        // PERBAIKAN: Menggunakan nama variabel camelCase
        $mataPelajaran = MataPelajaran::all(); 
        return view('admin.mata_pelajaran.index', compact('mataPelajaran'));
    }

    public function storeMapel(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required|string|unique:mata_pelajaran,nama_mapel',
        ]);

        MataPelajaran::create([
            'nama_mapel' => $request->nama_mapel,
        ]);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function updateMapel(Request $request, $id)
    {
        $mapel = MataPelajaran::find($id);
        if (!$mapel) {
            return redirect()->back()->with('error', 'Mata pelajaran tidak ditemukan.');
        }

        $request->validate([
            'nama_mapel' => ['required', 'string', Rule::unique('mata_pelajaran')->ignore($mapel->id)],
        ]);

        $mapel->update([
            'nama_mapel' => $request->nama_mapel,
        ]);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui!');
    }

    public function destroyMapel($id)
    {
        $mapel = MataPelajaran::find($id);
        if (!$mapel) {
            return redirect()->back()->with('error', 'Mata pelajaran tidak ditemukan.');
        }

        $mapel->delete();

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus!');
    }






    /* ==================================
     * JADWAL MANAGEMENT (WEB VERSION)
     * ================================== */
    public function indexJadwal(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        $kelas = Kelas::findOrFail($kelasId);

        $jadwal = JadwalMapelKelas::with(['kelas', 'mataPelajaran', 'guru'])
                    ->where('kelas_id', $kelasId)
                    ->orderBy('hari')
                    ->orderBy('jam_mulai')
                    ->get();
        
        $guruOptions = User::where('role', 'guru')->get();
        $mapelOptions = MataPelajaran::all();

        return view('admin.jadwal.index', compact('jadwal', 'kelas', 'guruOptions', 'mapelOptions'));
    }

    public function storeJadwal(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'required|exists:users,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        JadwalMapelKelas::create($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function updateJadwal(Request $request, $id)
    {
        $jadwal = JadwalMapelKelas::find($id);
        if (!$jadwal) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan.');
        }

        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'required|exists:users,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'sometimes|nullable|date_format:H:i',
            'jam_selesai' => 'sometimes|nullable|date_format:H:i|after:jam_mulai',
        ]);

        $jadwal->update($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui!');
    }

    public function destroyJadwal($id)
    {
        $jadwal = JadwalMapelKelas::find($id);
        if (!$jadwal) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan.');
        }

        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }
}