<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalMapelKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function getJadwalHariIni(Request $request)
    {
        try {
            // 1. Ambil ID Guru dari token otentikasi
            $guruId = Auth::user()->id;
            
            // 2. Tentukan hari saat ini dalam format yang sesuai dengan database.
            // Asumsi database Anda menggunakan format Bahasa Indonesia (Senin, Selasa, dst.).
            Carbon::setLocale('id');
            $hariIni = Carbon::now('Asia/Makassar')->dayName; 
            $sekarang = Carbon::now('Asia/Makassar');

            // 3. Ambil jadwal dari database dengan Eager Loading
            $jadwal = JadwalMapelKelas::where('guru_id', $guruId)
                ->where('hari', $hariIni)
                ->with(['kelas', 'mataPelajaran']) // Ambil relasi nama
                ->orderBy('jam_mulai', 'asc')
                ->get();

            if ($jadwal->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada jadwal mengajar hari ini.',
                    'data' => []
                ], 200);
            }

            // 4. Format data dan tentukan status aktif/lewat
            $data = $jadwal->map(function ($item) use ($sekarang) {
                
                // Ambil jam selesai jadwal
                $jamSelesaiJadwal = Carbon::parse($item->jam_selesai);
                
                // Logika Aktivasi: Jadwal dianggap aktif jika jam sekarang belum melewati jam selesai + 15 menit toleransi.
                $isPassed = $sekarang->greaterThan($jamSelesaiJadwal->copy()->addMinutes(15));
                $statusText = $isPassed ? 'Selesai' : 'Aktif';

                return [
                    'jadwal_id' => $item->id,
                    'kelas_nama' => $item->kelas->nama_kelas,
                    'mapel_nama' => $item->mataPelajaran->nama_mapel,
                    'jam_mulai' => $item->jam_mulai,
                    'jam_selesai' => $item->jam_selesai,
                    'status_jadwal' => $statusText,
                    'is_active' => !$isPassed // Boolean flag untuk mobile
                ];
            });

            return response()->json([
                'message' => 'Daftar jadwal mengajar hari ini berhasil diambil.',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Error fetching guru schedule: ' . $e->getMessage()); 
            
            return response()->json([
                'message' => 'Terjadi kesalahan pada server saat mengambil data jadwal.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}