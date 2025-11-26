<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    public function run()
    {
        DB::table('kelas')->insert([
            ['nama_kelas' => 'VII A', 'wali_kelas_id' => 1, 'created_at' => now()],
            ['nama_kelas' => 'VII B', 'wali_kelas_id' => 2, 'created_at' => now()],
        ]);
    }
}
