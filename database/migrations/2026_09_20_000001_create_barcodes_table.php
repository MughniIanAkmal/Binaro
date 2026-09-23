<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barcodes') && !Schema::hasTable('barcode')) {
            Schema::create('barcodes', function (Blueprint $table) {
                $table->id('id_barcode');

                // 1 siswa = 1 QR code (relasi 1:1 sesuai ERD)
                $table->foreignId('id_siswa')
                    ->unique()
                    ->constrained('siswas', 'id_siswa')
                    ->cascadeOnDelete();

                // isi QR sekaligus kode cadangan yang diketik jika QR rusak
                $table->string('kode', 20)->unique();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('barcodes');
    }
};
