<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create or adapt quiz table (so materi can reference id_quiz optionally)
        if (!Schema::hasTable('quiz')) {
            Schema::create('quiz', function (Blueprint $table) {
                $table->id('id_quiz');
                $table->foreignId('id_mapel')->nullable()->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
                $table->foreignId('id_guru')->nullable()->constrained('guru', 'id_guru')->cascadeOnDelete();
                $table->unsignedBigInteger('id_sub_bab')->nullable();
                $table->unsignedBigInteger('id_materi')->nullable();
                $table->string('nama_quiz', 150)->nullable();
                $table->string('judul_quiz', 150)->nullable();
                $table->timestamps();

                $table->foreign('id_sub_bab')
                      ->references('id_sub_bab')
                      ->on('sub_bab')
                      ->onDelete('cascade');
            });
        } else {
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
                    $table->foreign('id_sub_bab')
                          ->references('id_sub_bab')
                          ->on('sub_bab')
                          ->onDelete('cascade');
                }
                if (!Schema::hasColumn('quiz', 'id_materi')) {
                    $table->unsignedBigInteger('id_materi')->nullable()->after('id_sub_bab');
                }
                if (!Schema::hasColumn('quiz', 'judul_quiz')) {
                    $table->string('judul_quiz', 150)->nullable()->after('id_materi');
                }
            });
        }

        // 2. Alter materi table
        Schema::table('materi', function (Blueprint $table) {
            if (!Schema::hasColumn('materi', 'tipe_materi')) {
                $table->enum('tipe_materi', ['video', 'dokumen', 'kuis'])->default('video')->after('isi_materi');
            }
            if (!Schema::hasColumn('materi', 'url_video')) {
                $table->string('url_video', 255)->nullable()->after('tipe_materi');
            }
            if (!Schema::hasColumn('materi', 'file_pdf')) {
                $table->string('file_pdf', 255)->nullable()->after('url_video');
            }
            if (!Schema::hasColumn('materi', 'id_quiz')) {
                $table->unsignedBigInteger('id_quiz')->nullable()->after('file_pdf');
                $table->foreign('id_quiz')
                      ->references('id_quiz')
                      ->on('quiz')
                      ->onDelete('set null');
            }
        });

        // 3. Create soal_quiz table
        if (!Schema::hasTable('soal_quiz')) {
            Schema::create('soal_quiz', function (Blueprint $table) {
                $table->id('id_soal');
                $table->unsignedBigInteger('id_quiz');
                $table->text('pertanyaan');
                $table->string('opsi_a', 255);
                $table->string('opsi_b', 255);
                $table->string('opsi_c', 255);
                $table->string('opsi_d', 255);
                $table->enum('kunci_jawaban', ['A', 'B', 'C', 'D']);
                $table->timestamps();

                $table->foreign('id_quiz')
                      ->references('id_quiz')
                      ->on('quiz')
                      ->onDelete('cascade');
            });
        }

        // 4. Create hasil_kuis_siswa table
        if (!Schema::hasTable('hasil_kuis_siswa')) {
            Schema::create('hasil_kuis_siswa', function (Blueprint $table) {
                $table->id('id_hasil');
                $table->unsignedBigInteger('id_quiz');
                $table->unsignedBigInteger('id_siswa');
                $table->integer('jumlah_benar');
                $table->integer('jumlah_salah');
                $table->decimal('nilai_akhir', 5, 2);
                $table->timestamps();

                $table->unique(['id_quiz', 'id_siswa']);

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

    public function down(): void
    {
        Schema::dropIfExists('hasil_kuis_siswa');
        Schema::dropIfExists('soal_quiz');

        Schema::table('materi', function (Blueprint $table) {
            if (Schema::hasColumn('materi', 'id_quiz')) {
                $table->dropForeign(['id_quiz']);
                $table->dropColumn('id_quiz');
            }
            if (Schema::hasColumn('materi', 'file_pdf')) {
                $table->dropColumn('file_pdf');
            }
            if (Schema::hasColumn('materi', 'url_video')) {
                $table->dropColumn('url_video');
            }
            if (Schema::hasColumn('materi', 'tipe_materi')) {
                $table->dropColumn('tipe_materi');
            }
        });

        Schema::dropIfExists('quiz');
    }
};
