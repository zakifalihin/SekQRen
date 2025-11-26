<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UsersTableSeeder::class,
            MataPelajaranSeeder::class,
            KelasSeeder::class,
            SiswaSeeder::class,
            JadwalSeeder::class,
            AbsensiGuruSeeder::class,
            AbsensiSiswaSeeder::class,
            RekapExportSeeder::class,
            RiwayatAbsenSeeder::class,
        ]);
    }
}
