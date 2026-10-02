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
        if (!Schema::hasTable('soal_quiz') || !Schema::hasColumn('soal_quiz', 'id_soal')) {
            return;
        }

        $indexes = DB::select(
            'SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['soal_quiz']
        );
        $hasLeadingIdIndex = collect($indexes)->contains(
            fn ($index) => $index->COLUMN_NAME === 'id_soal' && (int) $index->SEQ_IN_INDEX === 1
        );

        if (!$hasLeadingIdIndex) {
            $hasPrimaryKey = collect($indexes)->contains(fn ($index) => $index->INDEX_NAME === 'PRIMARY');
            $keyType = $hasPrimaryKey ? 'UNIQUE KEY `soal_quiz_id_soal_unique`' : 'PRIMARY KEY';
            DB::statement("ALTER TABLE `soal_quiz` ADD {$keyType} (`id_soal`)");
        }

        DB::statement('ALTER TABLE `soal_quiz` MODIFY `id_soal` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }
        if (Schema::hasTable('soal_quiz') && Schema::hasColumn('soal_quiz', 'id_soal')) {
            DB::statement('ALTER TABLE `soal_quiz` MODIFY `id_soal` BIGINT UNSIGNED NOT NULL');
        }
    }
};