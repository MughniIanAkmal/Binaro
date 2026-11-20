<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semester aktif adalah pengaturan SATU sekolah, bukan per kelas:
     * satu sekolah tidak mungkin berjalan di dua semester berbeda.
     * Nilai dipindah ke tabel pengaturan (kunci "semester_aktif"),
     * kolom per kelas dihapus agar tidak bisa tidak sinkron.
     */
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique();
            $table->string('nilai')->nullable();
        });

        DB::table('pengaturan')->insert([
            'kunci' => 'semester_aktif',
            'nilai' => '1',
        ]);

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('semester_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester_aktif')->default(1)->after('pararel');
        });

        Schema::dropIfExists('pengaturan');
    }
};
