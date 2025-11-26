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
        Schema::create('absensi_guru', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke tabel users (guru_id)
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Alpa']);
            $table->time('jam_datang')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->decimal('total_jam_ajar', 4, 2)->nullable();
            $table->timestamps();

            // Index gabungan untuk performa
            $table->index(['guru_id', 'tanggal'], 'idx_tanggal_guru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_guru');
    }
};