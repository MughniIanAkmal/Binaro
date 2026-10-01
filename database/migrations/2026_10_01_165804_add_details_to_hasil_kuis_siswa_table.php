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
        Schema::table('hasil_kuis_siswa', function (Blueprint $table) {
            $table->integer('waktu_menit')->nullable()->after('nilai_akhir');
            $table->integer('nilai_keaktifan')->nullable()->after('waktu_menit');
            $table->text('catatan_guru')->nullable()->after('nilai_keaktifan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_kuis_siswa', function (Blueprint $table) {
            $table->dropColumn(['waktu_menit', 'nilai_keaktifan', 'catatan_guru']);
        });
    }
};
