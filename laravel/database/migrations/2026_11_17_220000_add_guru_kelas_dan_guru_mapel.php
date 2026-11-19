<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revisi 17 Nov 2026:
     *  2. Guru di-lock ke 1 kelas (guru.id_rooms).
     *  3. Admin mengatur mapel yang diajar guru (pivot guru_mapel).
     * Kolom dibuat nullable supaya guru lama yang belum diatur admin
     * tetap bisa login — lock baru berlaku setelah admin mengisinya.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'id_rooms')) {
                $table->unsignedBigInteger('id_rooms')->nullable()->after('id_guru');
            }
        });

        // FK best-effort (abaikan kalau driver / data lama tidak memungkinkan).
        try {
            Schema::table('guru', function (Blueprint $table) {
                $table->foreign('id_rooms')->references('id_rooms')->on('kelas')->nullOnDelete();
            });
        } catch (Throwable $e) {
        }

        if (! Schema::hasTable('guru_mapel')) {
            Schema::create('guru_mapel', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_guru');
                $table->unsignedBigInteger('id_mapel');
                $table->timestamps();
                $table->foreign('id_guru')->references('id_guru')->on('guru')->cascadeOnDelete();
                $table->foreign('id_mapel')->references('id_mapel')->on('mata_pelajaran')->cascadeOnDelete();
                $table->unique(['id_guru', 'id_mapel']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_mapel');

        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'id_rooms')) {
                try {
                    $table->dropForeign(['id_rooms']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('id_rooms');
            }
        });
    }
};
