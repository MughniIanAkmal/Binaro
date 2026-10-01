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
        // 1. Tambah kolom pada tabel quiz
        Schema::table('quiz', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz', 'tingkat_level')) {
                $table->enum('tingkat_level', ['mudah', 'sedang', 'susah'])->default('sedang')->after('judul_quiz');
            }
            if (!Schema::hasColumn('quiz', 'durasi_menit')) {
                $table->integer('durasi_menit')->default(60)->after('tingkat_level');
            }
            if (!Schema::hasColumn('quiz', 'target_tipe')) {
                $table->enum('target_tipe', ['semua', 'pilihan'])->default('semua')->after('durasi_menit');
            }
            if (!Schema::hasColumn('quiz', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('target_tipe');
            }
        });

        // 2. Tambah kolom pada tabel soal_quiz
        Schema::table('soal_quiz', function (Blueprint $table) {
            if (!Schema::hasColumn('soal_quiz', 'gambar')) {
                $table->string('gambar', 255)->nullable()->after('pertanyaan');
            }
            if (!Schema::hasColumn('soal_quiz', 'bobot_nilai')) {
                $table->integer('bobot_nilai')->default(10)->after('kunci_jawaban');
            }
        });

        // 3. Buat tabel pivot target siswa untuk ujian online
        if (!Schema::hasTable('quiz_target_siswa')) {
            Schema::create('quiz_target_siswa', function (Blueprint $table) {
                $table->unsignedBigInteger('id_quiz');
                $table->unsignedBigInteger('id_siswa');
                $table->timestamps();

                $table->primary(['id_quiz', 'id_siswa']);

                $table->foreign('id_quiz')
                      ->references('id_quiz')
                      ->on('quiz')
                      ->onDelete('cascade');

                $table->foreign('id_siswa')
                      ->references('id_siswa')
                      ->on('siswa')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_target_siswa');

        Schema::table('soal_quiz', function (Blueprint $table) {
            if (Schema::hasColumn('soal_quiz', 'gambar')) {
                $table->dropColumn('gambar');
            }
            if (Schema::hasColumn('soal_quiz', 'bobot_nilai')) {
                $table->dropColumn('bobot_nilai');
            }
        });

        Schema::table('quiz', function (Blueprint $table) {
            if (Schema::hasColumn('quiz', 'tingkat_level')) {
                $table->dropColumn('tingkat_level');
            }
            if (Schema::hasColumn('quiz', 'durasi_menit')) {
                $table->dropColumn('durasi_menit');
            }
            if (Schema::hasColumn('quiz', 'target_tipe')) {
                $table->dropColumn('target_tipe');
            }
            if (Schema::hasColumn('quiz', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
        });
    }
};
