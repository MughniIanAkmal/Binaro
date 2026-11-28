<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relasi kelas ke mata pelajaran: 1 mapel = 1 kelas pengampu
     * (mapel yang sama di kelas lain dibuat sebagai entri terpisah).
     * Guru pengampu tetap disimpan di pivot guru_mapel.
     * Kolom nullable supaya mapel lama tetap valid sampai admin
     * mengisi kelasnya lewat halaman pengelolaan mapel.
     */
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (! Schema::hasColumn('mata_pelajaran', 'id_rooms')) {
                $table->unsignedBigInteger('id_rooms')->nullable()->after('nama_mapel');
                $table->index('id_rooms');
            }
        });

        // FK best-effort (abaikan kalau driver / data lama tidak memungkinkan).
        try {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->foreign('id_rooms')->references('id_rooms')->on('kelas')->nullOnDelete();
            });
        } catch (Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (Schema::hasColumn('mata_pelajaran', 'id_rooms')) {
                try {
                    $table->dropForeign(['id_rooms']);
                    $table->dropIndex(['id_rooms']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('id_rooms');
            }
        });
    }
};
