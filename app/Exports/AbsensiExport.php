<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiExport implements FromCollection, WithHeadings
{
    protected $guruId, $startDate, $endDate;

    public function __construct($guruId, $startDate, $endDate) {
        $this->guruId = $guruId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection() {
        return DB::table('absensi_siswa')
            ->join('jadwal_mapel_kelas', 'absensi_siswa.jadwal_mapel_kelas_id', '=', 'jadwal_mapel_kelas.id')
            ->join('siswa', 'absensi_siswa.siswa_id', '=', 'siswa.id')
            ->join('mata_pelajaran', 'jadwal_mapel_kelas.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->where('jadwal_mapel_kelas.guru_id', $this->guruId)
            ->whereBetween('absensi_siswa.tanggal', [$this->startDate, $this->endDate])
            ->select('siswa.nama', 'absensi_siswa.tanggal', 'mata_pelajaran.nama_mapel', 'absensi_siswa.status')
            ->get();
    }

    public function headings(): array {
        return ["Nama Siswa", "Tanggal", "Mata Pelajaran", "Status"];
    }
}