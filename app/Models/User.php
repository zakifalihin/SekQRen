<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Controllers\Api\AbsensiController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'nip',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    // ---------------- RELASI ----------------
    // 1 Guru bisa punya banyak Jadwal
    public function jadwalMapelKelas()
    {
        return $this->hasMany(JadwalMapelKelas::class, 'guru_id', 'id');
    }   


    // 1 Guru bisa punya banyak Absensi
    public function absensis()
    {
        return $this->hasMany(AbsensiController::class, 'guru_id');
    }

    // 1 Guru bisa mengajar di banyak Kelas
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'guru_id');
    }

    public function kelasYangDiajar()
    {
        return $this->belongsToMany(Kelas::class, 'jadwal_mapel_kelas', 'guru_id', 'kelas_id')
                    ->with('matapelajaran') // Kita bisa sekalian mengambil data mapel
                    ->distinct(); // Pastikan setiap kelas hanya muncul sekali
    }
}