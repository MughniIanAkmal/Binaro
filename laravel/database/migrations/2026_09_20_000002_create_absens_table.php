<?php

use Illuminate\Database\Migrations\Migration;

/*
 * MIGRATION DIPINDAHKAN → 2026_01_01_000001_create_academic_system_tables.php
 *
 * Migration ini awalnya dimaksudkan untuk membuat tabel 'absens' sebagai
 * asumsi sementara. Namun migrasi utama (academic_system_tables) sudah
 * membuat tabel 'absen' dengan skema yang benar. Guard clause di bawah
 * memastikan tidak terjadi bentrok saat migrate — tabel 'absens' TIDAK
 * akan pernah terbuat di DB yang sehat.
 *
 * File ini sengaja dipertahankan agar sejarah migration tetap utuh.
 * Tidak perlu menghapusnya dari folder database/migrations/.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tabel 'absen' sudah dibuat oleh 2026_01_01_000001_create_academic_system_tables.php.
        // Biarkan saja — jangan buat duplikat bernama 'absens'.
    }

    public function down(): void
    {
        // Tidak ada yang perlu dibatalkan; tabel asli tetap ada.
    }
};
