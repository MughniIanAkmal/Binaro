<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bab kini termasuk semester ganjil (1) atau genap (2).
     * Bab lama otomatis masuk semester ganjil.
     */
    public function up(): void
    {
        Schema::table('bab', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester')->default(1)->after('nama_bab');
        });
    }

    public function down(): void
    {
        Schema::table('bab', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }
};
