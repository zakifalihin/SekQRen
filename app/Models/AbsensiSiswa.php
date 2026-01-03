<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSiswa extends Model
{
    use HasFactory;

    protected $table = 'absensi_siswa';
    
    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'mapel_id',
        'guru_id',       // WAJIB: Penambahan untuk Akuntabilitas Guru
        'tanggal',
        'status',
        'keterangan',    // WAJIB: Penambahan untuk catatan Guru
        'waktu_scan',
        'is_validated'   // Opsional: Untuk fitur Guru Kontrak/Audit
    ];
    
    public $timestamps = true;

    // --- Definisi Relasi (Penting untuk Laporan Task B1) ---

    // Relasi 1: Absensi ini milik Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Relasi 2: Absensi ini terkait dengan Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Relasi 3: Absensi ini terkait dengan Mata Pelajaran
    public function mataPelajaran()
    {
        // Pastikan nama FK sesuai dengan skema Anda (mapel_id)
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    // Relasi 4: Absensi ini dicatat oleh Guru Penanggung Jawab
    public function guru()
    {
        // Karena Guru ada di tabel 'users'
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalMapelKelas::class, 'jadwal_mapel_kelas_id', 'id');
    }
}