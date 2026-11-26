<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('siswa', 'id_mapel')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropForeign(['id_mapel']);
                $table->dropColumn('id_mapel');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('siswa', 'id_mapel')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->foreignId('id_mapel')->nullable()->after('id_rooms')
                    ->constrained('mata_pelajaran', 'id_mapel')->nullOnDelete();
            });
        }
    }
};
