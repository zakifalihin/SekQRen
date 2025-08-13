<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KelasSeeder extends Seeder
{
    public function run()
    {
        DB::table('kelas')->insert([
            [
                'nama_kelas' => 'Kelas 2A',
                'wali_kelas_id' => 3, // pastikan user dengan id=3 ada
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}
