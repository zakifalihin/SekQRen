<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $fillable = ['nama_mapel'];

    public function jadwal()
    {
        return $this->hasMany(JadwalMapelKelas::class, 'mata_pelajaran_id');
    }
}
