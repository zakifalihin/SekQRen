<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiSiswaSeeder extends Seeder
{
    public function run()
    {
        DB::table('absensi_siswa')->insert([
            [
                'siswa_id' => 1,
                'kelas_id' => 1,
                'mapel_id' => 1,
                'guru_id' => 1,
                'tanggal' => now()->toDateString(),
                'status' => 'Hadir',
                'waktu_scan' => '07:10',
                'keterangan' => '',
                'created_at' => now(),
            ],
        ]);
    }
}
