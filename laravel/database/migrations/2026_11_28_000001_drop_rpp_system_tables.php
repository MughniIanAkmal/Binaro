<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur RPP (Modul Ajar) dihapus seluruhnya dari kode (routes, controller,
     * view, API) per keputusan pengguna. Migration ini menghapus tabel-tabelnya
     * dari database. Data RPP di dalamnya hilang permanen.
     * Urutan drop anak -> induk agar foreign key tidak menggagal.
     */
    public function up(): void
    {
        Schema::dropIfExists('rpp_catatan');
        Schema::dropIfExists('rpp_version_history');
        Schema::dropIfExists('rpp_sessions');
        Schema::dropIfExists('cp_master');
        Schema::dropIfExists('rpp');
    }

    public function down(): void
    {
        // Sengaja tidak ada: penghapusan fitur RPP tidak di-rollback.
        // Tabel bisa dibuat ulang lewat migrasi lama bila fitur dihidupkan lagi.
    }
};
