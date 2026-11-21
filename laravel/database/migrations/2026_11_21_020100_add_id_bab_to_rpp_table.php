<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RPP/Modul Ajar disusun per unit pembelajaran (per bab), bukan per mapel.
     * Kolom ini menghubungkan dokumen ke bab yang diajarkan sehingga guru dan
     * admin bisa melihat bab mana yang sudah punya RPP dan mana yang belum.
     */
    public function up(): void
    {
        Schema::table('rpp', function (Blueprint $table) {
            $table->foreignId('id_bab')->nullable()->after('id_mapel')
                ->constrained('bab', 'id_bab')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rpp', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_bab');
        });
    }
};
