<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiSiswaExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        // Data yang sudah diformat dari Controller LaporanController
        $this->data = $data; 
    }

    // Metode FromCollection: Mengembalikan data yang akan diexport
    public function collection()
    {
        return collect($this->data);
    }

    // Metode WithHeadings: Mendefinisikan header kolom di Excel
    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu Scan',
            'Status',
            'Keterangan',
            'NISN Siswa',
            'Nama Siswa',
            'Kelas',
            'Mata Pelajaran',
            'Guru Penanggung Jawab',
        ];
    }
}