<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $gurus = [
            [
                'nama' => 'Ahmad Fauzi',
                'nip' => '198501012010011001',
                'password' => Hash::make('password123'),
                'email' => 'ahmad.fauzi@example.com',
                'role' => 'guru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Siti Aminah',
                'nip' => '197912142010012002',
                'password' => Hash::make('password123'),
                'email' => 'siti.aminah@example.com',
                'role' => 'guru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Budi Santoso',
                'nip' => '198208152010013003',
                'password' => Hash::make('password123'),
                'email' => 'budi.santoso@example.com',
                'role' => 'guru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Dewi Lestari',
                'nip' => '198707102010014004',
                'password' => Hash::make('password123'),
                'email' => 'dewi.lestari@example.com',
                'role' => 'guru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Rudi Hartono',
                'nip' => '197605202010015005',
                'password' => Hash::make('password123'),
                'email' => 'rudi.hartono@example.com',
                'role' => 'guru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($gurus);
    }
}
