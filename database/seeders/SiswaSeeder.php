<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        $siswaData = [
            [
                'nama' => 'Siti Aminah',
                'nisn' => '1234567890',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Merdeka No.1',
                'kelas_id' => 1,
            ],
            [
                'nama' => 'Budi Santoso',
                'nisn' => '9876543210',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Pahlawan No.2',
                'kelas_id' => 1,
            ],
        ];

        foreach ($siswaData as $data) {
            // Generate token unik
            $token = strtoupper(Str::random(8));

            // Simpan ke DB
            $siswaId = DB::table('siswa')->insertGetId([
                'nama' => $data['nama'],
                'nisn' => $data['nisn'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'alamat' => $data['alamat'],
                'kelas_id' => $data['kelas_id'],
                'qr_token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Path penyimpanan QR code
            $path = 'qrcodes/' . $token . '.png';
            $url  = url('/absensi/scan/' . $token);

            // Generate QR
            QrCode::format('png')
                ->size(300)
                ->generate($url, public_path($path));

            // Update path QR code di DB
            DB::table('siswa')->where('id', $siswaId)->update([
                'qr_code' => $path
            ]);
        }
    }
}
