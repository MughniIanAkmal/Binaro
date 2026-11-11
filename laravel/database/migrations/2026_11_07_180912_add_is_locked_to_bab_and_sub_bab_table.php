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
        Schema::table('bab', function (Blueprint $table) {
            if (!Schema::hasColumn('bab', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('nama_bab');
            }
        });

        Schema::table('sub_bab', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_bab', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('nama_sub_bab');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bab', function (Blueprint $table) {
            if (Schema::hasColumn('bab', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });

        Schema::table('sub_bab', function (Blueprint $table) {
            if (Schema::hasColumn('sub_bab', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
