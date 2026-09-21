<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('absen');
        Schema::dropIfExists('rpp');
        Schema::dropIfExists('hasil_nilai');
        Schema::dropIfExists('nilai');
        Schema::dropIfExists('quiz');
        Schema::dropIfExists('pr');
        Schema::dropIfExists('materi');
        Schema::dropIfExists('sub_bab');
        Schema::dropIfExists('bab');
        Schema::dropIfExists('jadwal_mata_pelajaran');
        Schema::dropIfExists('barcode');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('admin');
        Schema::enableForeignKeyConstraints();

        // 1. Admin
        Schema::create('admin', function (Blueprint $table) {
            $table->id('id_admin');
            $table->string('nip', 30)->unique();
            $table->string('nama_admin', 100);
            $table->string('password', 255);
            $table->timestamps();
        });

        // 2. Guru
        Schema::create('guru', function (Blueprint $table) {
            $table->id('id_guru');
            $table->string('nip', 30)->unique();
            $table->string('nama_guru', 100);
            $table->string('no_hp', 20)->nullable();
            $table->timestamps();
        });

        // 3. Mata Pelajaran
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id('id_mapel');
            $table->string('nama_mapel', 100);
        });

        // 4. Kelas
        Schema::create('kelas', function (Blueprint $table) {
            $table->id('id_rooms');
            $table->string('pararel', 50);
        });

        // 5. Siswa
        Schema::create('siswa', function (Blueprint $table) {
            $table->id('id_siswa');
            $table->foreignId('id_mapel')->nullable()->constrained('mata_pelajaran', 'id_mapel')->nullOnDelete();
            $table->foreignId('id_rooms')->nullable()->constrained('kelas', 'id_rooms')->nullOnDelete();
            $table->string('nisn', 30)->unique();
            $table->string('nm_siswa', 100);
            $table->string('no_hp', 20)->nullable();
            $table->string('password', 255);
            $table->timestamps();
        });

        // 6. Barcode
        Schema::create('barcode', function (Blueprint $table) {
            $table->id('id_barcode');
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->cascadeOnDelete();
            $table->string('kode_barcode', 100)->unique();
            $table->timestamps();
        });

        // 7. Jadwal Mata Pelajaran
        Schema::create('jadwal_mata_pelajaran', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->foreignId('id_guru')->constrained('guru', 'id_guru')->cascadeOnDelete();
            $table->foreignId('id_rooms')->nullable()->constrained('kelas', 'id_rooms')->nullOnDelete();
            $table->string('hari', 20);
            $table->string('jam', 50);
        });

        // 8. BAB
        Schema::create('bab', function (Blueprint $table) {
            $table->id('id_bab');
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->string('nama_bab', 150);
        });

        // 9. Sub BAB
        Schema::create('sub_bab', function (Blueprint $table) {
            $table->id('id_sub_bab');
            $table->foreignId('id_bab')->constrained('bab', 'id_bab')->cascadeOnDelete();
            $table->string('nama_sub_bab', 150);
        });

        // 10. Materi
        Schema::create('materi', function (Blueprint $table) {
            $table->id('id_materi');
            $table->foreignId('id_sub_bab')->constrained('sub_bab', 'id_sub_bab')->cascadeOnDelete();
            $table->foreignId('id_bab')->constrained('bab', 'id_bab')->cascadeOnDelete();
            $table->string('judul_materi', 200);
            $table->text('isi_materi')->nullable();
            $table->timestamps();
        });

        // 11. PR
        Schema::create('pr', function (Blueprint $table) {
            $table->id('id_pr');
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->foreignId('id_guru')->constrained('guru', 'id_guru')->cascadeOnDelete();
            $table->string('nama_pr', 150);
            $table->text('deskripsi')->nullable();
            $table->dateTime('tgl_tenggat');
            $table->timestamps();
        });

        // 12. Quiz
        Schema::create('quiz', function (Blueprint $table) {
            $table->id('id_quiz');
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->foreignId('id_guru')->constrained('guru', 'id_guru')->cascadeOnDelete();
            $table->string('nama_quiz', 150);
            $table->timestamps();
        });

        // 13. Nilai
        Schema::create('nilai', function (Blueprint $table) {
            $table->id('id_nilai');
            $table->foreignId('id_pr')->nullable()->constrained('pr', 'id_pr')->cascadeOnDelete();
            $table->foreignId('id_quiz')->nullable()->constrained('quiz', 'id_quiz')->cascadeOnDelete();
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->decimal('skor', 5, 2)->default(0.00);
        });

        // 14. Hasil Nilai
        Schema::create('hasil_nilai', function (Blueprint $table) {
            $table->id('id_hasil_nilai');
            $table->foreignId('id_nilai')->constrained('nilai', 'id_nilai')->cascadeOnDelete();
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->cascadeOnDelete();
            $table->string('data_nilai', 100)->nullable();
            $table->decimal('total_nilai', 5, 2)->default(0.00);
        });

        // 15. RPP (Modul Ajar)
        Schema::create('rpp', function (Blueprint $table) {
            $table->id('id_rpp');
            $table->foreignId('id_guru')->constrained('guru', 'id_guru')->cascadeOnDelete();
            $table->foreignId('id_rooms')->nullable()->constrained('kelas', 'id_rooms')->nullOnDelete();
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->string('judul_rpp', 150);
            $table->text('deskripsi')->nullable();
            $table->json('komponen_checklist')->nullable();
            $table->enum('status', ['draft', 'menunggu_review', 'terverifikasi', 'perlu_revisi'])->default('draft');
            $table->text('catatan_revisi')->nullable();
            $table->string('file_rpp', 255)->nullable();
            $table->timestamps();
        });

        // 16. Absen
        Schema::create('absen', function (Blueprint $table) {
            $table->id('id_absen');
            $table->foreignId('id_guru')->constrained('guru', 'id_guru')->cascadeOnDelete();
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->cascadeOnDelete();
            $table->foreignId('id_barcode')->nullable()->constrained('barcode', 'id_barcode')->nullOnDelete();
            $table->enum('metode', ['scan_qr', 'manual_guru'])->default('scan_qr');
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpa'])->default('Hadir');
            $table->string('keterangan', 255)->nullable();
            $table->string('berkas_surat', 255)->nullable();
            $table->timestamp('waktu_absen')->useCurrent();
            $table->date('tanggal');
            $table->unique(['id_siswa', 'tanggal'], 'uq_siswa_tanggal');
        });

        // 17. Notifikasi
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('id_guru')->nullable()->constrained('guru', 'id_guru')->nullOnDelete();
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->cascadeOnDelete();
            $table->foreignId('id_pr')->nullable()->constrained('pr', 'id_pr')->nullOnDelete();
            $table->text('pesan');
            $table->boolean('status_baca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('absen');
        Schema::dropIfExists('rpp');
        Schema::dropIfExists('hasil_nilai');
        Schema::dropIfExists('nilai');
        Schema::dropIfExists('quiz');
        Schema::dropIfExists('pr');
        Schema::dropIfExists('materi');
        Schema::dropIfExists('sub_bab');
        Schema::dropIfExists('bab');
        Schema::dropIfExists('jadwal_mata_pelajaran');
        Schema::dropIfExists('barcode');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('admin');
        Schema::enableForeignKeyConstraints();
    }
};
