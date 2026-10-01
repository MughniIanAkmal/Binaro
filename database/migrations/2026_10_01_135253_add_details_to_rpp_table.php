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
        Schema::table('rpp', function (Blueprint $table) {
            $table->index('id_guru');
            $table->dropUnique('uq_guru_mapel');

            $table->string('alokasi_waktu', 100)->nullable()->after('deskripsi');
            $table->string('fase', 50)->nullable()->default('Fase B')->after('alokasi_waktu');
            $table->string('modul_ke', 50)->nullable()->after('fase');
            $table->string('target_jadwal', 150)->nullable()->after('modul_ke');
            $table->string('ruang', 100)->nullable()->after('target_jadwal');
            $table->string('target_tanggal', 50)->nullable()->after('ruang');
            $table->unsignedBigInteger('id_jadwal')->nullable()->after('id_rooms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rpp', function (Blueprint $table) {
            $table->dropColumn([
                'alokasi_waktu',
                'fase',
                'modul_ke',
                'target_jadwal',
                'ruang',
                'target_tanggal',
                'id_jadwal',
            ]);
            $table->unique(['id_guru', 'id_mapel'], 'uq_guru_mapel');
            $table->dropIndex(['id_guru']);
        });
    }
};

