<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiGuru extends Model
{
    use HasFactory;

    protected $table = 'absensi_guru';
    
    // Kolom yang dapat diisi
    protected $fillable = [
        'guru_id',
        'tanggal',
        'status',
        'jam_datang',
        'jam_pulang',
        'total_jam_ajar',
        'keterangan'
    ];

    public $timestamps = true;
    // Relasi: Absensi ini dicatat oleh Guru (berelasi ke tabel users)
    public function guru()
    {
        // Asumsi Guru disimpan di App\Models\User
        return $this->belongsTo(User::class, 'guru_id');
    }
}