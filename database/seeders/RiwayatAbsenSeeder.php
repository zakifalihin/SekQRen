<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiwayatAbsenSeeder extends Seeder
{
    public function run()
    {
        DB::table('riwayat_absen')->insert([
            [
                'user_id' => 1,
                'tipe' => 'absensi_guru',
                'keterangan' => 'Guru melakukan check-in',
                'waktu' => now(),
                'created_at' => now(),
            ]
        ]);
    }
}
