<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalMapelKelas;
use App\Models\Kelas;
use App\Models\Siswa; 
use App\Models\AbsensiSiswa; 
use App\Models\User; 
use Carbon\Carbon;

class GuruController extends Controller
{
    /**
     * Mengambil data dashboard untuk guru yang sedang login.
     * Endpoint: GET /api/guru/dashboard
     */
    public function dashboard(Request $request)
    {
        $guru = $request->user();

        $hariIni = now()->format('l'); 

        $hariIndonesia = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hariQuery = $hariIndonesia[$hariIni] ?? $hariIni;

        // 1. Ambil jadwal hari ini untuk ditampilkan di list
        $jadwals = JadwalMapelKelas::where('guru_id', $guru->id)
            ->where('hari', $hariQuery)
            ->with(['kelas:id,nama_kelas', 'mataPelajaran:id,nama_mapel'])
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // 2. Hitung Total Siswa (Dari semua kelas yang diajar guru ini)
        $kelasIds = $guru->jadwalMapelKelas()->distinct()->pluck('kelas_id');
        $totalSiswa = Siswa::whereIn('kelas_id', $kelasIds)->count();
        
        // 3. PERBAIKAN TOTAL HADIR (Gunakan jadwal_mapel_kelas_id)
        // Ambil semua ID Jadwal milik guru ini
        $jadwalIds = JadwalMapelKelas::where('guru_id', $guru->id)->pluck('id');

        // Query ke tabel absensi_siswa menggunakan kolom yang benar
        $totalHadir = AbsensiSiswa::whereIn('jadwal_mapel_kelas_id', $jadwalIds)
            ->where('tanggal', Carbon::today())
            ->where('status', 'Hadir')
            ->count();
        
        return response()->json([
            'status' => 'success',
            'guru' => [
                'nama' => $guru->nama,
                'foto_url' => $guru->foto_url ?? null,
            ],
            'summary' => [
                'kelas_hari_ini' => $jadwals->count(),
                'total_hadir' => $totalHadir, 
                'total_siswa' => $totalSiswa,
            ],
            'jadwal_hari_ini' => $jadwals->map(function ($jadwal) {
                $now = now()->format('H:i:s');
                $status = 'Selesai';
                
                if ($now >= $jadwal->jam_mulai && $now <= $jadwal->jam_selesai) {
                    $status = 'Aktif';
                } elseif ($now < $jadwal->jam_mulai) {
                    $status = 'Akan Datang';
                }

                return [
                    'subject' => $jadwal->mataPelajaran->nama_mapel ?? '-',
                    'class'   => $jadwal->kelas->nama_kelas ?? '-',
                    'time'    => substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5),
                    'status'  => $status,
                ];
            }),
        ]);
    }


    /**
     * Mengambil daftar semua kelas yang diajar oleh guru.
     * Endpoint: GET /api/guru/kelas
     */
    public function daftarKelas(Request $request)
    {
        $guru = $request->user();

        if (!$guru) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $kelasIds = JadwalMapelKelas::where('guru_id', $guru->id)
            ->distinct()
            ->pluck('kelas_id');

        $kelasDiajar = Kelas::whereIn('id', $kelasIds)
            ->withCount('siswa')
            ->get();
        
        $data = $kelasDiajar->map(function ($kelas) use ($guru) {
            $jadwal = JadwalMapelKelas::where('guru_id', $guru->id)
                        ->where('kelas_id', $kelas->id)
                        ->with('mataPelajaran:id,nama_mapel')
                        ->first();

            return [
                'id'             => $kelas->id,
                'nama_kelas'     => $kelas->nama_kelas, 
                'nama_mapel'     => $jadwal->mataPelajaran->nama_mapel ?? 'Mapel Umum',
                'jumlah_siswa'   => $kelas->siswa_count,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * Mengambil detail mata pelajaran dan jadwal per kelas.
     * Endpoint: GET /api/guru/kelas/{kelas_id}/mapel
     */
    public function getMataPelajaranByKelas(Request $request, $kelas_id)
    {
        $guru = $request->user();

        // 1. Validasi Kelas
        $kelas = Kelas::find($kelas_id);
        if (!$kelas) {
             return response()->json([
                'status' => 'error',
                'message' => 'Kelas tidak ditemukan.'
            ], 404);
        }
        
        // 2. Ambil Jadwal
        $jadwals = JadwalMapelKelas::where('kelas_id', $kelas_id)
            ->where('guru_id', $guru->id)
            ->with(['mataPelajaran:id,nama_mapel', 'guru:id,nama']) 
            ->orderBy('hari', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        if ($jadwals->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada jadwal mata pelajaran yang ditemukan untuk kelas ini.',
                'data' => []
            ], 200);
        }

        // 3. Format Response
        $data = $jadwals->map(function ($jadwal) {
            $guruPengampu = $jadwal->guru->nama ?? 'N/A';
            $namaMapel = $jadwal->mataPelajaran->nama_mapel ?? 'Mata Pelajaran Tidak Dikenal';

            return [
                'id'             => $jadwal->id, // ID Jadwal
                'nama_mapel'     => $namaMapel,
                'guru_pengampu'  => $guruPengampu, 
                'hari'           => $jadwal->hari,
                'jam_mulai'      => substr($jadwal->jam_mulai, 0, 5),
                'jam_selesai'    => substr($jadwal->jam_selesai, 0, 5),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Detail mata pelajaran berhasil dimuat.',
            'data' => $data
        ]);
    }


    /**
     * Mengambil daftar siswa per kelas.
     * Endpoint: GET /api/guru/kelas/{kelas_id}/siswa
     */
    public function getSiswaByKelas(Request $request, $kelas_id)
    {
        $kelas = Kelas::find($kelas_id);
        if (!$kelas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas tidak ditemukan.'
            ], 404);
        }
        
        $siswaList = $kelas->siswa()
            ->select('id', 'nama', 'nisn') 
            ->orderBy('nama', 'asc')
            ->get();

        $data = $siswaList->map(function ($siswa) {
            return [
                'id' => $siswa->id, 
                'nama' => $siswa->nama, 
                'nisn' => $siswa->nisn, 
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data siswa berhasil dimuat.',
            'data' => $data
        ]);
    }
}