<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama_mapel',
    ];

    /**
     * Relasi ke jadwal mapel.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jadwal()
    {
        return $this->hasMany(JadwalMapelKelas::class, 'mata_pelajaran_id');
    }
}