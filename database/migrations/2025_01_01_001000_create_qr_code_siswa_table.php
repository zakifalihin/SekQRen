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
        Schema::create('qr_code_siswa', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke tabel siswa
            $table->foreignId('siswa_id')->unique()->constrained('siswa')->onDelete('cascade'); // Unique key
            $table->string('qr_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_code_siswa');
    }
};