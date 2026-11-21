<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan supervisi disimpan sebagai riwayat, bukan satu kolom teks yang
     * saling menimpa. Setiap catatan menempel pada versi dokumen saat dibuat
     * sehingga guru bisa melihat jejak umpan balik dari waktu ke waktu.
     */
    public function up(): void
    {
        Schema::create('rpp_catatan', function (Blueprint $table) {
            $table->id('id_catatan');
            $table->foreignId('id_rpp')->constrained('rpp', 'id_rpp')->cascadeOnDelete();
            $table->string('penulis_role', 20);
            $table->unsignedBigInteger('penulis_id')->nullable();
            $table->string('penulis_nama', 100)->nullable();
            $table->string('status_saat_itu', 30)->nullable();
            $table->text('isi');
            $table->timestamps();

            $table->index(['id_rpp', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpp_catatan');
    }
};
