<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiGuru extends Model
{
    use HasFactory;

    protected $table = 'absensi_guru';

    protected $fillable = [
        'guru_id',
        'tanggal',
        'status',
        'jam_datang',
        'jam_pulang'
    ];

    public $timestamps = true;


    // relasi ke user (guru)
    public function guru()
    {
        return $this->belongsTo(\App\Models\User::class, 'guru_id');
    }
}
