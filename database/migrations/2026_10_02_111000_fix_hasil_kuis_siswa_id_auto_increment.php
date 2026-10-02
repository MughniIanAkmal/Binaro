<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }
        if (!Schema::hasTable('hasil_kuis_siswa') || !Schema::hasColumn('hasil_kuis_siswa', 'id_hasil')) {
            return;
        }

        $indexes = DB::select(
            'SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['hasil_kuis_siswa']
        );
        $hasLeadingIdIndex = collect($indexes)->contains(
            fn ($index) => $index->COLUMN_NAME === 'id_hasil' && (int) $index->SEQ_IN_INDEX === 1
        );

        if (!$hasLeadingIdIndex) {
            $hasPrimaryKey = collect($indexes)->contains(fn ($index) => $index->INDEX_NAME === 'PRIMARY');
            $keyType = $hasPrimaryKey ? 'UNIQUE KEY `hasil_kuis_siswa_id_hasil_unique`' : 'PRIMARY KEY';
            DB::statement("ALTER TABLE `hasil_kuis_siswa` ADD {$keyType} (`id_hasil`)");
        }

        DB::statement('ALTER TABLE `hasil_kuis_siswa` MODIFY `id_hasil` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }
        if (Schema::hasTable('hasil_kuis_siswa') && Schema::hasColumn('hasil_kuis_siswa', 'id_hasil')) {
            DB::statement('ALTER TABLE `hasil_kuis_siswa` MODIFY `id_hasil` BIGINT UNSIGNED NOT NULL');
        }
    }
};