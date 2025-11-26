<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'nama' => 'Budi Santoso',
                'nip' => '198877665',
                'password' => Hash::make('password'),
                'email' => 'budi@guru.com',
                'role' => 'guru',
                'created_at' => now(),
            ],
            [
                'nama' => 'Ani Lestari',
                'nip' => '199112223',
                'password' => Hash::make('password'),
                'email' => 'ani@guru.com',
                'role' => 'guru',
                'created_at' => now(),
            ],
            [
                'nama' => 'Kepala Sekolah',
                'nip' => '100000001',
                'password' => Hash::make('password'),
                'email' => 'kepsek@school.com',
                'role' => 'kepala_sekolah',
                'created_at' => now(),
            ],
        ]);
    }
}
