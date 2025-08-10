<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Admins
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Users (Guru & Kepsek)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nip')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->enum('role', ['guru', 'kepala_sekolah']);
            $table->timestamps();
        });

        // Kelas
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');
            $table->unsignedBigInteger('wali_kelas_id');
            $table->foreign('wali_kelas_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Siswa
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nisn')->unique();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat');
            $table->unsignedBigInteger('kelas_id');
            $table->string('qr_code')->unique();
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->timestamps();
        });

        // Mata Pelajaran
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mapel');
            $table->timestamps();
        });

        // Jadwal Mapel Kelas
        Schema::create('jadwal_mapel_kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('guru_id');
            $table->enum('hari', ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id')->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('guru_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Absensi Siswa
        Schema::create('absensi_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('mapel_id');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Alpa']);
            $table->time('waktu_scan')->nullable();
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id')->on('mata_pelajaran')->onDelete('cascade');
            $table->timestamps();
        });

        // Absensi Guru
        Schema::create('absensi_guru', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Alpa']);
            $table->time('waktu_scan')->nullable();
            $table->foreign('guru_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // QR Code Siswa Backup
        Schema::create('qr_code_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id')->unique();
            $table->string('qr_code');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->timestamps();
        });

        // Riwayat Absen
        Schema::create('riwayat_absen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('tipe'); // siswa/guru
            $table->text('keterangan');
            $table->timestamp('waktu');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Rekap Export
        Schema::create('rekap_export', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exported_by');
            $table->string('jenis');
            $table->string('format');
            $table->timestamp('waktu_export');
            $table->foreign('exported_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_export');
        Schema::dropIfExists('riwayat_absen');
        Schema::dropIfExists('qr_code_siswa');
        Schema::dropIfExists('absensi_guru');
        Schema::dropIfExists('absensi_siswa');
        Schema::dropIfExists('jadwal_mapel_kelas');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('users');
        Schema::dropIfExists('admins');
    }
};
