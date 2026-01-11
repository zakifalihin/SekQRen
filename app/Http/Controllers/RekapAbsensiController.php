<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
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
        // Gunakan format Y-m-d jika kolom 'tanggal' di DB bertipe DATE
        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate   = Carbon::parse($request->end_date)->format('Y-m-d');
        
        $query = \App\Models\AbsensiGuru::with('guru')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($request->guru_id) {
            $query->where('guru_id', $request->guru_id);
        }

        // Sorting agar data yang hadir paling awal muncul di atas
        $query->orderBy('tanggal', 'desc') // Biasanya laporan terbaru di atas
            ->orderByRaw("CASE WHEN jam_datang IS NULL THEN 1 ELSE 0 END")
            ->orderBy('jam_datang', 'asc');

        return $query;
    }

    public function getLaporanGuru(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $data = $this->getAbsensiGuruQuery($request)
            ->get()
            ->map(function ($row) {
                // 1. Ambil semua ID Jadwal di mana guru ini sudah melakukan absensi siswa pada tanggal tersebut
                $jadwalIds = \App\Models\AbsensiSiswa::where('tanggal', $row->tanggal)
                    ->whereHas('jadwal', function($q) use ($row) {
                        $q->where('guru_id', $row->guru_id);
                    })
                    ->distinct('jadwal_mapel_kelas_id')
                    ->pluck('jadwal_mapel_kelas_id');

                // 2. Hitung total durasi jam dari jadwal-jadwal tersebut
                $totalMenit = 0;
                $jadwalTerlaksana = \App\Models\JadwalMapelKelas::whereIn('id', $jadwalIds)->get();

                foreach ($jadwalTerlaksana as $j) {
                    $mulai = \Carbon\Carbon::parse($j->jam_mulai);
                    $selesai = \Carbon\Carbon::parse($j->jam_selesai);
                    // Menghitung selisih dalam menit
                    $totalMenit += $mulai->diffInMinutes($selesai);
                }

                // 3. Konversi menit ke Jam (misal 120 menit jadi 2.00 jam)
                $totalJamAjar = $totalMenit / 60;

                return [
                    'guru_id'        => $row->guru_id,
                    'tanggal'        => \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y'),
                    'guru_nama'      => $row->guru->nama ?? '-',
                    'status'         => $row->status,
                    'jam_datang'     => $row->jam_datang ? \Carbon\Carbon::parse($row->jam_datang)->format('H:i') : '-', 
                    'jam_pulang'     => $row->jam_pulang ? \Carbon\Carbon::parse($row->jam_pulang)->format('H:i') : '-',
                    'total_jam_ajar' => number_format($totalJamAjar, 2, '.', ''),
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

        // Ambil data absensi guru sesuai filter
        $data = $this->getAbsensiGuruQuery($request)->get();

        $filename = 'Laporan_Absensi_Guru_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Tanggal', 'Nama Guru', 'Status', 'Jam Datang', 'Jam Pulang', 'Total Jam Ajar', 'Keterangan']);

        foreach ($data as $row) {
            // --- LOGIKA PERHITUNGAN JAM AJAR (Sama dengan getLaporanGuru) ---
            $jadwalIds = \App\Models\AbsensiSiswa::where('tanggal', $row->tanggal)
                ->whereHas('jadwal', function($q) use ($row) {
                    $q->where('guru_id', $row->guru_id);
                })
                ->distinct('jadwal_mapel_kelas_id')
                ->pluck('jadwal_mapel_kelas_id');

            $totalMenit = 0;
            $jadwalTerlaksana = \App\Models\JadwalMapelKelas::whereIn('id', $jadwalIds)->get();

            foreach ($jadwalTerlaksana as $j) {
                $mulai = \Carbon\Carbon::parse($j->jam_mulai);
                $selesai = \Carbon\Carbon::parse($j->jam_selesai);
                $totalMenit += $mulai->diffInMinutes($selesai);
            }
            
            $totalJamAjar = $totalMenit / 60;
            // -----------------------------------------------------------------

            fputcsv($csv, [
                $row->tanggal,
                $row->guru->nama ?? 'N/A',
                $row->status,
                $row->jam_datang ?? '-',
                $row->jam_pulang ?? '-',
                number_format($totalJamAjar, 2, '.', ''), // Masukkan hasil hitungan ke sini
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

        // Mapping data untuk Excel/CSV
        $dataExport = $results->map(function ($item, $key) {
            return [
                'No'             => $key + 1,
                // PERBAIKAN: Format tanggal di sini agar tidak muncul angka aneh
                'Tanggal'        => \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y'),
                'Waktu'          => $item->waktu_absen ?? '-',
                'NISN'           => $item->siswa->nisn ?? '-',
                'Nama Siswa'     => $item->siswa->nama ?? '-',
                'Kelas'          => $item->jadwal->kelas->nama_kelas ?? '-',
                'Mapel'          => $item->jadwal->mataPelajaran->nama_mapel ?? '-',
                'Status'         => $item->status,
                'Guru PJ'        => $item->jadwal->guru->nama ?? '-',
                'Keterangan'     => $item->keterangan ?? '-',
            ];
        });

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AbsensiSiswaExport($dataExport), 
            'Laporan_Absensi_Siswa_'.date('Ymd_His').'.xlsx'
        );
    }
}