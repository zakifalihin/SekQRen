<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiSiswa extends Model
{
    protected $table = 'absensi_siswa';
    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'mapel_id',
        'tanggal',
        'status',
        'waktu_scan'
    ];
    public $timestamps = true; // karena ada created_at & updated_at
}
