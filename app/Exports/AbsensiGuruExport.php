<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiGuruExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(Collection $data)
    {
        // Data sudah diformat dan divalidasi oleh Controller API
        $this->data = $data; 
    }

    // Metode FromCollection: Mengembalikan data untuk diexport
    public function collection()
    {
        return $this->data;
    }

    // Metode WithHeadings: Mendefinisikan header kolom di Excel
    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Guru',
            'Status Harian',
            'Jam Datang',
            'Jam Pulang',
            'Total Jam Ajar (Jam)',
            'Keterangan'
        ];
    }
}