<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use App\Models\AbsensiSiswa;
use Carbon\Carbon;


class RekapAbsensiController extends Controller
{

    // =========================================================
    // SECTION: ABSENSI GURU WEB
    // =========================================================

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






    // =========================================================
    // SECTION: ABSENSI SISWA WEB
    // =========================================================

    public function siswaIndex()
    {
        // Mengirim data dropdown untuk filter di UI
        return view('admin.operasional.siswa', [
            'guru'  => User::where('role', 'guru')->orderBy('nama', 'asc')->get(),
            'kelas' => Kelas::orderBy('nama_kelas', 'asc')->get(),
            'mapel' => MataPelajaran::orderBy('nama_mapel', 'asc')->get(),
        ]);
    }

    // app/Http/Controllers/RekapAbsensiController.php

    public function getAbsensiSiswaWeb(Request $request)
    {
        // Eager Loading relasi: Absensi -> Jadwal -> (Kelas, Mapel, Guru/User)
        $query = \App\Models\AbsensiSiswa::with([
            'siswa', 
            'jadwal.kelas', 
            'jadwal.mataPelajaran', 
            'jadwal.guru'
        ]);

        // 1. Filter Rentang Tanggal (Wajib)
        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
        $query->whereBetween('tanggal', [$startDate, $endDate]);

        // 2. Filter Dropdown (Kelas & Mapel)
        if ($request->filled('kelas_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }
        if ($request->filled('mapel_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('mata_pelajaran_id', $request->mapel_id));
        }

        // 3. FITUR: Filter Guru PJ
        if ($request->filled('guru_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('guru_id', $request->guru_id));
        }

        // 4. FITUR: Sorting Dinamis (Termasuk Guru PJ)
        $sortField = $request->get('sort_by', 'tanggal'); 
        $sortOrder = $request->get('order', 'desc');

        if ($sortField === 'guru_pj') {
            // Sorting berdasarkan nama guru di tabel users melalui join jadwal
            $query->join('jadwal_mapel_kelas', 'absensi_siswa.jadwal_mapel_kelas_id', '=', 'jadwal_mapel_kelas.id')
                ->join('users', 'jadwal_mapel_kelas.guru_id', '=', 'users.id')
                ->orderBy('users.nama', $sortOrder)
                ->select('absensi_siswa.*'); // Pastikan ID yang diambil tetap ID absensi
        } else {
            $query->orderBy($sortField, $sortOrder);
        }

        $results = $query->get();

        $data = $results->map(function ($item) {
            return [
                'tanggal'        => $item->tanggal,
                'waktu_scan'     => $item->waktu_absen, // Kolom database: waktu_absen
                'siswa' => [
                    'nisn' => $item->siswa->nisn ?? '-',
                    'nama' => $item->siswa->nama ?? '-',
                ],
                'kelas'          => $item->jadwal->kelas->nama_kelas ?? '-', 
                'mata_pelajaran' => $item->jadwal->mataPelajaran->nama_mapel ?? '-',
                'status'         => $item->status,
                'guru_pj'        => $item->jadwal->guru->nama ?? '-',
            ];
        });

        return response()->json(['data' => $data]);
    }


        /**
     * Fungsi pembantu untuk menyamakan query antara View dan Export
     */
    private function baseAbsensiQuery(Request $request)
    {
        $query = \App\Models\AbsensiSiswa::with([
            'siswa', 
            'jadwal.kelas', 
            'jadwal.mataPelajaran', 
            'jadwal.guru'
        ]);

        // Filter Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        // Filter Dropdown
        if ($request->filled('kelas_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }
        if ($request->filled('mapel_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('mata_pelajaran_id', $request->mapel_id));
        }
        if ($request->filled('guru_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('guru_id', $request->guru_id));
        }

        // LOGIKA SORTING (Sesuai dengan yang ada di tabel)
        $sortField = $request->get('sort_by', 'tanggal'); 
        $sortOrder = $request->get('order', 'desc');

        if ($sortField === 'guru_pj') {
            $query->join('jadwal_mapel_kelas', 'absensi_siswa.jadwal_mapel_kelas_id', '=', 'jadwal_mapel_kelas.id')
                ->join('users', 'jadwal_mapel_kelas.guru_id', '=', 'users.id')
                ->orderBy('users.nama', $sortOrder)
                ->select('absensi_siswa.*');
        } else {
            $query->orderBy($sortField, $sortOrder);
        }

        return $query;
    }

    public function exportAbsensiSiswaWeb(Request $request)
    {
        // Mengambil query yang sudah difilter dan di-sort
        $results = $this->baseAbsensiQuery($request)->get();

        // Mapping data untuk Excel
        $dataExport = $results->map(function ($item, $key) {
            return [
                'No'             => $key + 1,
                'Tanggal'        => $item->tanggal,
                'Waktu'          => $item->waktu_absen,
                'NISN'           => $item->siswa->nisn ?? '-',
                'Nama Siswa'     => $item->siswa->nama ?? '-',
                'Kelas'          => $item->jadwal->kelas->nama_kelas ?? '-',
                'Mapel'          => $item->jadwal->mataPelajaran->nama_mapel ?? '-',
                'Status'         => $item->status,
                'Guru PJ'        => $item->jadwal->guru->nama ?? '-',
                'Keterangan'     => $item->keterangan ?? '-',
            ];
        });

        // Gunakan library Excel pilihan Anda (Maatwebsite/Excel)
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AbsensiSiswaExport($dataExport), 
            'Laporan_Absensi_Siswa_'.date('Ymd_His').'.xlsx'
        );
    }
}