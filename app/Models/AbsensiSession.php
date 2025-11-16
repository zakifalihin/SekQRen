<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSession extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'absensi_sessions';
    
    // Kolom yang dapat diisi melalui mass assignment
    protected $fillable = [
        'token',
        'guru_id',
        'jadwal_id',
        'is_expired',
        'expires_at',
    ];

    // Kolom yang harus dikonversi ke instance Carbon (Date/Time)
    protected $dates = ['expires_at']; 

    // Relasi: Session ini dimiliki oleh satu Guru
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    // Relasi: Session ini terkait dengan satu Jadwal Mapel Kelas
    public function jadwal()
    {
        return $this->belongsTo(JadwalMapelKelas::class, 'jadwal_id');
    }
}