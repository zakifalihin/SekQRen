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
        Schema::create('rekap_export', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke tabel users (exported_by)
            $table->foreignId('exported_by')->constrained('users')->onDelete('cascade');
            $table->string('jenis');
            $table->string('format');
            $table->timestamp('waktu_export')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_export');
    }
};