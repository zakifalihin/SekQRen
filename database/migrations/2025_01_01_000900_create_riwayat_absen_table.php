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
        Schema::create('riwayat_absen', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke tabel users (user_id)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipe');
            $table->text('keterangan');
            $table->timestamp('waktu')->useCurrent()->useCurrentOnUpdate(); // Sesuai dengan DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_absen');
    }
};