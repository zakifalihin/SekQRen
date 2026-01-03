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
        Schema::create('jadwal_mapel_kelas', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke tabel kelas
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            // Foreign Key ke tabel mata_pelajaran
            $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajaran')->onDelete('cascade');
            // Foreign Key ke tabel users (guru_id)
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            
            // 🚀 TAMBAHAN KOLOM UNTUK SESI ABSENSI (Manual & Sync)
            $table->string('session_token', 32)->nullable();
            $table->timestamp('token_expired_at')->nullable();
            
            $table->timestamps();

            // Index untuk kolom hari
            $table->index('hari', 'idx_hari');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_mapel_kelas');
    }
};