<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalMapelKelas;
use App\Models\Kelas;

class GuruController extends Controller
{
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

        $jadwals = \App\Models\JadwalMapelKelas::where('guru_id', $guru->id)
            ->where('hari', $hariQuery)
            ->with(['kelas:id,nama_kelas', 'mataPelajaran:id,nama_mapel'])
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $kelasHariIni = $jadwals->count();

        $kelasIds = $guru->jadwalMapelKelas()->distinct()->pluck('kelas_id');
        $totalSiswa = \App\Models\Siswa::whereIn('kelas_id', $kelasIds)->count();

        $totalHadir = 0;

        return response()->json([
            'status' => 'success',
            'guru' => [
                'nama' => $guru->nama,
                'foto_url' => $guru->foto_url ?? null,
            ],
            'summary' => [
                'kelas_hari_ini' => $kelasHariIni,
                'total_hadir' => $totalHadir,
                'total_siswa' => $totalSiswa,
            ],
            'jadwal_hari_ini' => $jadwals->map(function ($jadwal) {
                $now = now()->format('H:i:s');
                $status = 'Selesai';
                if ($now < $jadwal->jam_selesai) {
                    $status = 'Akan Datang';
                }
                if ($now >= $jadwal->jam_mulai && $now <= $jadwal->jam_selesai) {
                    $status = 'Aktif';
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
     * Mengambil daftar semua kelas yang diajar oleh guru. (VERSI PERBAIKAN FINAL)
     */
    public function daftarKelas(Request $request)
    {
        // Mengambil data guru dari request yang sudah divalidasi oleh Sanctum
        $guru = $request->user();

        // PENGECEKAN PENTING: Jika Sanctum gagal, $guru akan null
        if (!$guru) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Mengambil ID unik dari kelas-kelas yang diajar oleh guru
        $kelasIds = JadwalMapelKelas::where('guru_id', $guru->id)
            ->distinct()
            ->pluck('kelas_id');

        // Mengambil data kelas lengkap berdasarkan ID tersebut, beserta jumlah siswanya
        $kelasDiajar = Kelas::whereIn('id', $kelasIds)
            ->withCount('siswa')
            ->get();
        
        // Memformat data agar sesuai dengan kebutuhan aplikasi Flutter
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

        // Mengembalikan data dalam format JSON
        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
    }
}
