<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id('id_siswa');
            $table->string('nama');
            $table->string('nis')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('no_hp')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('username')->unique();
            $table->string('password');

            // Relasi ke tabel mata_pelajaran
            $table->foreignId('id_mapel')
                ->nullable()
                ->constrained('mata_pelajaran', 'id_mapel')
                ->nullOnDelete();

            // Relasi ke tabel kelas (rooms)
            $table->foreignId('id_rooms')
                ->nullable()
                ->constrained('kelas', 'id_rooms')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};