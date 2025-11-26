<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalSeeder extends Seeder
{
    public function run()
    {
        DB::table('jadwal_mapel_kelas')->insert([
            [
                'kelas_id' => 1,
                'mata_pelajaran_id' => 1,
                'guru_id' => 1,
                'hari' => 'Senin',
                'jam_mulai' => '07:00',
                'jam_selesai' => '08:30',
                'created_at' => now(),
            ],
            [
                'kelas_id' => 2,
                'mata_pelajaran_id' => 2,
                'guru_id' => 2,
                'hari' => 'Selasa',
                'jam_mulai' => '08:40',
                'jam_selesai' => '10:00',
                'created_at' => now(),
            ],
        ]);
    }
}
