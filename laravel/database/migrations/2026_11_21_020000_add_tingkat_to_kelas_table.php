<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase Kurikulum Merdeka diturunkan dari tingkat kelas, bukan diketik manual:
     * tingkat 1-2 = Fase A, 3-4 = Fase B, 5-6 = Fase C.
     *
     * Tingkat diambil dari angka pada kolom pararel ("Kelas 4B" -> 4) supaya
     * data kelas lama tetap terisi tanpa input ulang.
     */
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedTinyInteger('tingkat')->nullable()->after('pararel');
        });

        foreach (DB::table('kelas')->get(['id_rooms', 'pararel']) as $kelas) {
            if (preg_match('/(\d{1,2})/', (string) $kelas->pararel, $m)) {
                $tingkat = (int) $m[1];
                if ($tingkat >= 1 && $tingkat <= 12) {
                    DB::table('kelas')->where('id_rooms', $kelas->id_rooms)->update(['tingkat' => $tingkat]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('tingkat');
        });
    }
};
