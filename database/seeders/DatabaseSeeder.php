<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin default
        DB::table('admins')->insert([
            'nama' => 'Admin Sekolah',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'email' => 'admin@sekolah.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Guru & Kepala Sekolah
        DB::table('users')->insert([
            [
                'nama' => 'Budi Santoso',
                'nip' => '197801011',
                'password' => Hash::make('guru123'),
                'email' => 'budi@sekolah.com',
                'role' => 'guru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Ani Rahma',
                'nip' => '197801012',
                'password' => Hash::make('kepsek123'),
                'email' => 'ani@sekolah.com',
                'role' => 'kepala_sekolah',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Mata Pelajaran
        DB::table('mata_pelajaran')->insert([
            ['nama_mapel' => 'Bahasa Indonesia', 'created_at' => now(), 'updated_at' => now()],
            ['nama_mapel' => 'Matematika', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Kelas
        DB::table('kelas')->insert([
            ['nama_kelas' => 'X IPA 1', 'wali_kelas_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Siswa
        DB::table('siswa')->insert([
            [
                'nama' => 'Siti Aminah',
                'nisn' => '1234567890',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Merdeka No.1',
                'kelas_id' => 1,
                'qr_code' => 'QR123456',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // QR Code Backup
        DB::table('qr_code_siswa')->insert([
            'siswa_id' => 1,
            'qr_code' => 'QR123456',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
