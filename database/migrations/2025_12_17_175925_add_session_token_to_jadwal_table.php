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
        Schema::table('jadwal_mapel_kelas', function (Blueprint $table) {
            // Menambah kolom token dan waktu expired ke tabel jadwal
            $table->string('session_token', 32)->nullable();
            $table->timestamp('token_expired_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_mapel_kelas', function (Blueprint $table) {
            $table->dropColumn(['session_token', 'token_expired_at']);
        });
    }
};

