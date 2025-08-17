<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // Gunakan Storage
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $nisn = trim((string)($row['nisn'] ?? ''));
        $nomorTelepon = trim((string)($row['nomor_telepon'] ?? ''));
        $alamat = trim((string)($row['alamat'] ?? ''));
        $kelas = Kelas::where('nama_kelas', $row['nama_kelas'])->first();
        
        if (!$kelas) {
            return null;
        }

        $qrToken = Str::random(32);
        $qrCodeData = json_encode([
            'nisn' => $nisn,
            'token' => $qrToken,
            'kelas_id' => $kelas->id
        ]);
        
        // Perbaikan: Simpan file ke folder storage
        $filename = 'qrcodes/' . $nisn . '.png';
        Storage::disk('public')->put($filename, QrCode::format('png')->size(200)->generate($qrCodeData));

        return new Siswa([
            'nama' => $row['nama'],
            'nisn' => $nisn,
            'jenis_kelamin' => $row['jenis_kelamin'],
            'agama' => $row['agama'],
            'nomor_telepon' => $nomorTelepon,
            'alamat' => $alamat,
            'kelas_id' => $kelas->id,
            'qr_code' => $filename,
            'qr_token' => $qrToken,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string',
            'nisn' => 'required|unique:siswa,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nama_kelas' => 'required|exists:kelas,nama_kelas',
        ];
    }
}