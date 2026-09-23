<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * CATATAN: di ERD, kolom tabel Absen dan RPP tampak tertukar.
 * Migration ini adalah ASUMSI agar fitur scan bisa berjalan.
 * Samakan dengan tabel absen milik tim sebelum dipakai bersama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('absens') && !Schema::hasTable('absen')) {
            Schema::create('absens', function (Blueprint $table) {
                $table->id('id_absen');

                $table->foreignId('id_siswa')
                    ->constrained('siswas', 'id_siswa')
                    ->cascadeOnDelete();

                // guru yang melakukan scan (boleh kosong sampai login guru siap)
                $table->unsignedBigInteger('id_guru')->nullable();

                // QR yang dipakai (ERD: Absen "melalui" Barcode)
                $table->foreignId('id_barcode')->nullable()
                    ->constrained('barcodes', 'id_barcode')
                    ->nullOnDelete();

                $table->date('tanggal');
                $table->time('jam');
                $table->string('metode', 10)->default('scan'); // scan | manual
                $table->timestamps();

                // satu siswa hanya boleh absen sekali per hari
                $table->unique(['id_siswa', 'tanggal']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('absens');
    }
};
