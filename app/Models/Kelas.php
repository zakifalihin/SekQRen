<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'wali_kelas_id',
    ];

    // Relasi ke wali kelas (guru)
    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    // Relasi ke siswa
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    // Relasi ke matapelajaran
    public function matapelajaran()
    {
        return $this->hasMany(Matapelajaran::class, 'kelas_id');
    }

    // Relasi ke jadwalMapel
    public function jadwalMapel()
    {
        return $this->hasMany(JadwalMapelKelas::class, 'kelas_id');
    }
}
