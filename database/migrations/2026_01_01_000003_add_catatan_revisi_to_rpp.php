<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rpp', function (Blueprint $table) {
            if (!Schema::hasColumn('rpp', 'catatan_revisi')) {
                $table->text('catatan_revisi')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rpp', function (Blueprint $table) {
            if (Schema::hasColumn('rpp', 'catatan_revisi')) {
                $table->dropColumn('catatan_revisi');
            }
        });
    }
};