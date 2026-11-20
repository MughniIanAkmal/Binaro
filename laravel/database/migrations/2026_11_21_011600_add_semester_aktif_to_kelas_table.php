<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semester yang sedang berjalan untuk tiap kelas (1 = ganjil, 2 = genap).
     * Diatur admin; aplikasi siswa hanya menampilkan bab pada semester ini.
     */
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester_aktif')->default(1)->after('pararel');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('semester_aktif');
        });
    }
};
