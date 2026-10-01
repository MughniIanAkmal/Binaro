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
        Schema::table('hasil_kuis_siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('hasil_kuis_siswa', 'status_kirim')) {
                $table->boolean('status_kirim')->default(false)->after('catatan_guru');
            }
            if (!Schema::hasColumn('hasil_kuis_siswa', 'waktu_kirim')) {
                $table->timestamp('waktu_kirim')->nullable()->after('status_kirim');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_kuis_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('hasil_kuis_siswa', 'status_kirim')) {
                $table->dropColumn('status_kirim');
            }
            if (Schema::hasColumn('hasil_kuis_siswa', 'waktu_kirim')) {
                $table->dropColumn('waktu_kirim');
            }
        });
    }
};
