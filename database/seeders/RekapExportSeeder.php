<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekapExportSeeder extends Seeder
{
    public function run()
    {
        DB::table('rekap_export')->insert([
            [
                'exported_by' => 1,
                'jenis' => 'Absensi Siswa',
                'format' => 'Excel',
                'waktu_export' => now(),
                'created_at' => now(),
            ]
        ]);
    }
}
