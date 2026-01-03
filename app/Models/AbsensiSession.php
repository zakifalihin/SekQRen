<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSession extends Model
{
    use HasFactory;

    // Pastikan nama tabel benar
    protected $table = 'absensi_sessions';

    // ✅ WAJIB: Daftarkan semua kolom agar bisa di-insert
    protected $fillable = [
        'jadwal_id',      // Harus sama persis dengan nama kolom di database
        'guru_id',
        'session_token',
        'status',
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