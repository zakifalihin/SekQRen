<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaExportTemplate implements FromArray, WithHeadings
{
    /**
     * Data kosong untuk template, dengan kolom yang sesuai.
     * Baris ini akan menjadi contoh bagi admin untuk mengisi data.
     */
    public function array(): array
    {
        return [
            ['Nama Lengkap', '1921681000', 'L', 'Islam', '081234567890', 'Jl. Merdeka No. 1', 'MIPA 1']
        ];
    }

    /**
     * Nama-nama kolom (headers) untuk template Excel.
     * Harus sama persis dengan yang ada di rules validasi di SiswaImport.
     */
    public function headings(): array
    {
        return [
            'nama',
            'nisn',
            'jenis_kelamin',
            'agama',
            'nomor_telepon',
            'alamat',
            'nama_kelas'
        ];
    }
}