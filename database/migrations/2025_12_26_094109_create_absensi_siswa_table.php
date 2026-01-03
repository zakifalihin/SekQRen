<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('absensi_siswa', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel Siswa
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            
            // Relasi ke Jadwal (PENTING: Ini yang menghubungkan mapel, kelas, dan guru sekaligus)
            $table->foreignId('jadwal_mapel_kelas_id')->constrained('jadwal_mapel_kelas')->onDelete('cascade');
            
            $table->date('tanggal');
            $table->time('waktu_absen');
            
            // Status kehadiran
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Absen'])->default('Absen');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi_siswa');
    }
};