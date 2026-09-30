<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz', function (Blueprint $table) {
            if (Schema::hasColumn('quiz', 'id_mapel')) {
                $table->unsignedBigInteger('id_mapel')->nullable()->change();
            }
            if (Schema::hasColumn('quiz', 'id_guru')) {
                $table->unsignedBigInteger('id_guru')->nullable()->change();
            }
            if (Schema::hasColumn('quiz', 'nama_quiz')) {
                $table->string('nama_quiz', 150)->nullable()->change();
            }
            if (!Schema::hasColumn('quiz', 'id_sub_bab')) {
                $table->unsignedBigInteger('id_sub_bab')->nullable()->after('id_quiz');
            }
            if (!Schema::hasColumn('quiz', 'id_materi')) {
                $table->unsignedBigInteger('id_materi')->nullable()->after('id_sub_bab');
            }
            if (!Schema::hasColumn('quiz', 'judul_quiz')) {
                $table->string('judul_quiz', 150)->nullable()->after('id_materi');
            }
        });
    }

    public function down(): void
    {
        // Safe down
    }
};
