<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (!Schema::hasColumn('mata_pelajaran', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('nama_mapel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (Schema::hasColumn('mata_pelajaran', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
        });
    }
};
