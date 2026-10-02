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
        Schema::table('soal_quiz', function (Blueprint $table) {
            if (!Schema::hasColumn('soal_quiz', 'tingkat_kesulitan')) {
                $table->string('tingkat_kesulitan', 20)->default('sedang')->after('bobot_nilai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soal_quiz', function (Blueprint $table) {
            if (Schema::hasColumn('soal_quiz', 'tingkat_kesulitan')) {
                $table->dropColumn('tingkat_kesulitan');
            }
        });
    }
};
