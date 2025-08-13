<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';  // nama tabel

    // Kolom yang bisa diisi mass assignment
    protected $fillable = [
        'nama',
        'nisn',
        'jenis_kelamin',
        'alamat',
        'kelas_id',
        'qr_code',
        'qr_token',
    ];

    /**
     * Relasi ke kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
