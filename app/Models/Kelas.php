<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    // Kolom yang boleh diisi mass assignment
    protected $fillable = [
        'nama_kelas',
        'wali_kelas_id',
    ];

    /**
     * Relasi ke User (guru) yang menjadi wali kelas
     */
    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    /**
     * Relasi ke siswa-siswa yang ada di kelas ini
     */
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}
