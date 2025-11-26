<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiGuruSeeder extends Seeder
{
    public function run()
    {
        DB::table('absensi_guru')->insert([
            [
                'guru_id' => 1,
                'tanggal' => now()->toDateString(),
                'status' => 'Hadir',
                'jam_datang' => '07:05',
                'jam_pulang' => '14:00',
                'total_jam_ajar' => 6,
                'created_at' => now(),
            ],
        ]);
    }
}
