<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // ... (Fungsi guruIndex tidak berubah)

    public function guruIndex()
    {
        $dataGuru = User::where('role', 'guru')
                        ->orderBy('nama', 'asc')
                        ->get();

        return view('admin.operasional.guru', [
            'guru' => $dataGuru,
            'tanggal_hari_ini' => Carbon::now('Asia/Makassar')
                                        ->locale('id')
                                        ->isoFormat('D MMMM YYYY')
        ]);
    }

    /**
     * Helper untuk mendapatkan Query Absensi Guru dengan filter dan sorting yang benar
     */
    protected function getAbsensiGuruQuery(Request $request)
    {
        // === PERBAIKAN LOGIC FILTERING TANGGAL ===
        // 1. Pastikan tanggal mulai diambil dari 00:00:00
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        // 2. Pastikan tanggal akhir diambil sampai 23:59:59 (akhir hari)
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        
        $query = \App\Models\AbsensiGuru::with('guru')
            // Menggunakan whereBetween pada format DATETIME (tetapi ini juga berlaku untuk DATE)
            // Jika kolom 'tanggal' di DB Anda HANYA DATE, ini seharusnya tetap bekerja, 
            // namun menggunakan Carbon::endOfDay() adalah praktik yang lebih aman
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($request->guru_id) {
            $query->where('guru_id', $request->guru_id);
        }

        // === SORTING LENGKAP (Sudah Benar) ===
        $query->orderBy('tanggal', 'asc')
            ->orderByRaw("CASE WHEN jam_datang IS NULL THEN 1 ELSE 0 END")
            ->orderBy('jam_datang', 'asc')
            ->orderByRaw("CASE WHEN jam_pulang IS NULL THEN 1 ELSE 0 END")
            ->orderBy('jam_pulang', 'asc');

        return $query;
    }

    public function getLaporanGuru(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        // Menggunakan helper function yang sudah diperbaiki filtering-nya
        $data = $this->getAbsensiGuruQuery($request)
            ->get()
            ->map(function ($row) {
                return [
                    'guru_id'        => $row->guru_id,
                    'tanggal'        => $row->tanggal,
                    'guru_nama'      => $row->guru->nama ?? '-',
                    'status'         => $row->status,
                    'jam_datang'     => $row->jam_datang ?? null, 
                    'jam_pulang'     => $row->jam_pulang ?? null,
                    'total_jam_ajar' => $row->total_jam_ajar ?? "0.00",
                    'keterangan'     => $row->keterangan ?? '-',
                ];
            });

        return response()->json(['data' => $data]);
    }

    public function exportLaporanGuru(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        // Menggunakan helper function agar sorting konsisten
        $data = $this->getAbsensiGuruQuery($request)->get();

        $filename = 'Laporan_Absensi_Guru_' . date('Ymd_His') . '.csv';

        // Header CSV
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $csv = fopen('php://temp', 'r+');
        
        // Baris Header Kolom
        fputcsv($csv, ['Tanggal', 'Nama Guru', 'Status', 'Jam Datang', 'Jam Pulang', 'Total Jam Ajar', 'Keterangan']);

        // Data Baris
        foreach ($data as $row) {
            fputcsv($csv, [
                $row->tanggal,
                $row->guru->nama ?? 'N/A',
                $row->status,
                $row->jam_datang ?? '-',
                $row->jam_pulang ?? '-',
                $row->total_jam_ajar ?? '0.00',
                $row->keterangan ?? '-',
            ]);
        }

        rewind($csv);
        $csv_output = stream_get_contents($csv);
        fclose($csv);

        return response($csv_output, 200, $headers);
    }


    // ... (Fungsi siswaIndex tidak berubah)
    public function siswaIndex()
    {
        return view('admin.operasional.siswa', [
            'guru'  => User::where('role', 'guru')->orderBy('nama', 'asc')->get(),
            'kelas' => Kelas::orderBy('nama_kelas', 'asc')->get(),
            'mapel' => MataPelajaran::orderBy('nama_mapel', 'asc')->get(),
        ]);
    }
}