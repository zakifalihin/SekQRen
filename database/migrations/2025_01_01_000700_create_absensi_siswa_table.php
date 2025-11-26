<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi_siswa', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke tabel siswa
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            // Foreign Key ke tabel kelas
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            // Foreign Key ke tabel mata_pelajaran
            $table->foreignId('mapel_id')->constrained('mata_pelajaran')->onDelete('cascade');
            // Foreign Key ke tabel users (guru_id)
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Alpa']);
            $table->time('waktu_scan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Index untuk kolom tanggal
            $table->index('tanggal', 'idx_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa');
    }
};