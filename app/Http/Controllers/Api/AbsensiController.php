<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalMapelKelas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

class AbsensiController extends Controller
{
    public function getAktivitasHariIni(Request $request)
    {
        try {
            $guruId = $request->user()->id;
            $tanggalTerpilih = $request->query('tanggal', Carbon::today()->toDateString());
            $hariIni = Carbon::today()->toDateString();
            $jamSekarang = Carbon::now()->format('H:i:s');

            $jadwal = JadwalMapelKelas::with(['kelas', 'mataPelajaran'])
                ->where('guru_id', $guruId)
                ->get();

            $result = [];
            foreach ($jadwal as $item) {
                $totalSiswa = DB::table('siswa')->where('kelas_id', $item->kelas_id)->count();
                
                // Ambil statistik dan ambil jam update terakhir (updated_at) untuk sorting
                $stats = DB::table('absensi_siswa')
                    ->where('jadwal_mapel_kelas_id', $item->id)
                    ->whereDate('tanggal', $tanggalTerpilih)
                    ->selectRaw("
                        COUNT(CASE WHEN status = 'Hadir' THEN 1 END) as hadir,
                        COUNT(CASE WHEN status = 'Izin' THEN 1 END) as izin,
                        COUNT(CASE WHEN status = 'Alpha' THEN 1 END) as alpha,
                        MAX(updated_at) as last_activity
                    ")
                    ->first();

                // --- FILTER: HANYA TAMBAHKAN JIKA ADA PERUBAHAN (TOTAL ABSEN > 0) ---
                $totalAbsen = (int)$stats->hadir + (int)$stats->izin + (int)$stats->alpha;
                
                if ($totalAbsen > 0) {
                    // --- LOGIKA STATUS TETAP SAMA ---
                    $status = 'Selesai';
                    if ($tanggalTerpilih === $hariIni) {
                        if ($jamSekarang >= $item->jam_mulai && $jamSekarang <= $item->jam_selesai) {
                            $status = 'Berlangsung';
                        } elseif ($jamSekarang < $item->jam_mulai) {
                            $status = 'Belum Mulai';
                        }
                    } else if ($tanggalTerpilih > $hariIni) {
                        $status = 'Mendatang';
                    }

                    $result[] = [
                        'id_jadwal'    => $item->id,
                        'nama_mapel'   => $item->mataPelajaran->nama_mapel,
                        'nama_kelas'   => $item->kelas->nama_kelas,
                        'hari'         => $item->hari,
                        'jam_mulai'    => substr($item->jam_mulai, 0, 5),
                        'jam_selesai'  => substr($item->jam_selesai, 0, 5),
                        'total_siswa'  => (int) $totalSiswa,
                        'jumlah_hadir' => (int) $stats->hadir,
                        'jumlah_izin'  => (int) $stats->izin,
                        'jumlah_alpha' => (int) $stats->alpha,
                        'status'       => $status,
                        'last_activity' => $stats->last_activity // Simpan untuk sorting
                    ];
                }
            }

            // --- SORTING: YANG TERBARU DIATAS (BERDASARKAN LAST_ACTIVITY) ---
            usort($result, function ($a, $b) {
                return strcmp($b['last_activity'], $a['last_activity']);
            });

            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function exportRekap(Request $request) 
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $guruId = $request->user()->id;

        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Tanggal harus diisi'], 400);
        }

        $fileName = 'rekap_absensi_' . $startDate . '_to_' . $endDate . '.xlsx';

        // Ini akan mengirimkan file stream ke Flutter
        return Excel::download(new AbsensiExport($guruId, $startDate, $endDate), $fileName);
    }

    public function getDetailAbsensi($id_jadwal)
    {
        try {
            $detail = DB::table('absensi_siswa')
                ->join('siswa', 'absensi_siswa.siswa_id', '=', 'siswa.id')
                ->where('absensi_siswa.jadwal_mapel_kelas_id', $id_jadwal)
                ->whereDate('absensi_siswa.tanggal', now()->toDateString())
                ->select('absensi_siswa.id as id_absensi', 'siswa.nama', 'siswa.nisn', 'absensi_siswa.status')
                ->get();

            return response()->json(['status' => 'success', 'data' => $detail]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}