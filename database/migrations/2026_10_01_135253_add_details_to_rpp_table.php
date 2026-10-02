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
        // Index: best-effort, abaikan jika sudah ada / gagal di driver tertentu.
        try {
            Schema::table('rpp', function (Blueprint $table) {
                $table->index('id_guru');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('rpp', function (Blueprint $table) {
                $table->dropUnique('uq_guru_mapel');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('rpp', function (Blueprint $table) {
            if (! Schema::hasColumn('rpp', 'alokasi_waktu')) {
                $table->string('alokasi_waktu', 100)->nullable()->after('deskripsi');
            }
            if (! Schema::hasColumn('rpp', 'fase')) {
                $table->string('fase', 50)->nullable()->default('Fase B')->after('alokasi_waktu');
            }
            if (! Schema::hasColumn('rpp', 'modul_ke')) {
                $table->string('modul_ke', 50)->nullable()->after('fase');
            }
            if (! Schema::hasColumn('rpp', 'target_jadwal')) {
                $table->string('target_jadwal', 150)->nullable()->after('modul_ke');
            }
            if (! Schema::hasColumn('rpp', 'ruang')) {
                $table->string('ruang', 100)->nullable()->after('target_jadwal');
            }
            if (! Schema::hasColumn('rpp', 'target_tanggal')) {
                $table->string('target_tanggal', 50)->nullable()->after('ruang');
            }
            if (! Schema::hasColumn('rpp', 'id_jadwal')) {
                $table->unsignedBigInteger('id_jadwal')->nullable()->after('id_rooms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rpp', function (Blueprint $table) {
            foreach ([
                'alokasi_waktu',
                'fase',
                'modul_ke',
                'target_jadwal',
                'ruang',
                'target_tanggal',
                'id_jadwal',
            ] as $col) {
                if (Schema::hasColumn('rpp', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        try {
            Schema::table('rpp', function (Blueprint $table) {
                $table->unique(['id_guru', 'id_mapel'], 'uq_guru_mapel');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('rpp', function (Blueprint $table) {
                $table->dropIndex('rpp_id_guru_index');
            });
        } catch (\Throwable $e) {
        }
    }
};

