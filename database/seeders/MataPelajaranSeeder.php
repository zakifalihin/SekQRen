<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MataPelajaranSeeder extends Seeder
{
    public function run()
    {
        DB::table('mata_pelajaran')->insert([
            ['nama_mapel' => 'Matematika', 'created_at' => now()],
            ['nama_mapel' => 'Bahasa Indonesia', 'created_at' => now()],
            ['nama_mapel' => 'IPA', 'created_at' => now()],
            ['nama_mapel' => 'IPS', 'created_at' => now()],
            ['nama_mapel' => 'Pendidikan Agama', 'created_at' => now()],
        ]);
    }
}
