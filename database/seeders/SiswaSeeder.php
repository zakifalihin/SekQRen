<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        DB::table('siswa')->insert([
            [
                'nama' => 'Rizky Ramadhan',
                'nisn' => '0011223344',
                'nomor_telepon' => '08123456789',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'alamat' => 'Jalan Melati No. 10',
                'kelas_id' => 1,
                'qr_code' => 'QR-'.Str::random(10),
                'qr_token' => Str::random(32),
                'created_at' => now(),
            ],
            [
                'nama' => 'Dewi Lestari',
                'nisn' => '0011223355',
                'nomor_telepon' => '08223344556',
                'jenis_kelamin' => 'P',
                'agama' => 'Islam',
                'alamat' => 'Jalan Mawar No. 5',
                'kelas_id' => 2,
                'qr_code' => 'QR-'.Str::random(10),
                'qr_token' => Str::random(32),
                'created_at' => now(),
            ],
        ]);
    }
}
