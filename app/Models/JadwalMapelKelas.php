<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalMapelKelas extends Model
{
    use HasFactory;

    // PERBAIKAN: Menggunakan nama tabel yang benar
    protected $table = 'jadwal_mapel_kelas'; 

    protected $fillable = [
        'id',
        'kelas_id',
        'mata_pelajaran_id', 
        'guru_id',
        'hari',
        'jam_mulai',
        'jam_selesai'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
