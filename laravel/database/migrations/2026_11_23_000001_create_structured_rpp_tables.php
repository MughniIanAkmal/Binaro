<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rpp', function (Blueprint $table) {
            $table->unsignedInteger('alokasi_waktu_menit')->nullable();
            $table->unsignedSmallInteger('total_pertemuan')->default(1);
            $table->string('semester', 10)->nullable();
            $table->string('tahun_ajaran', 20)->nullable();
            $table->string('nomor_rpp', 30)->nullable()->unique();
        });

        Schema::create('cp_master', function (Blueprint $table) {
            $table->id('id_cp');
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->string('fase', 1);
            $table->string('elemen', 150)->nullable();
            $table->text('cp_text');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['id_mapel', 'fase', 'is_active']);
        });

        Schema::create('rpp_sessions', function (Blueprint $table) {
            $table->id('id_session');
            $table->foreignId('id_rpp')->constrained('rpp', 'id_rpp')->cascadeOnDelete();
            $table->unsignedSmallInteger('nomor_pertemuan');
            $table->json('konten')->nullable();
            $table->timestamps();
            $table->unique(['id_rpp', 'nomor_pertemuan']);
        });

        Schema::create('rpp_version_history', function (Blueprint $table) {
            $table->id('id_version');
            $table->foreignId('id_rpp')->constrained('rpp', 'id_rpp')->cascadeOnDelete();
            $table->unsignedInteger('versi');
            $table->json('snapshot');
            $table->string('perubahan_apa', 255)->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();
            $table->unique(['id_rpp', 'versi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpp_version_history');
        Schema::dropIfExists('rpp_sessions');
        Schema::dropIfExists('cp_master');

        Schema::table('rpp', function (Blueprint $table) {
            $table->dropUnique(['nomor_rpp']);
            $table->dropColumn(['alokasi_waktu_menit', 'total_pertemuan', 'semester', 'tahun_ajaran', 'nomor_rpp']);
        });
    }
};
