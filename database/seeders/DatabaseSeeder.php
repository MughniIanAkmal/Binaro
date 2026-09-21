<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Admin
        DB::table('admin')->truncate();
        DB::table('admin')->insert([
            [
                'id_admin' => 1,
                'nip' => '19850101001',
                'nama_admin' => 'Bpk. Ahmad Fauzi, S.Kom',
                'password' => Hash::make('admin123'),
                'created_at' => now(),
            ]
        ]);

        // 2. Guru
        DB::table('guru')->truncate();
        DB::table('guru')->insert([
            [
                'id_guru' => 1,
                'nip' => '19800101001',
                'nama_guru' => 'Siti Nurhaliza, S.Pd',
                'no_hp' => '081234567891',
                'password' => Hash::make('guru123'),
                'created_at' => now(),
            ],
            [
                'id_guru' => 2,
                'nip' => '19800101002',
                'nama_guru' => 'Budi Santoso, S.Pd',
                'no_hp' => '081234567892',
                'password' => Hash::make('guru123'),
                'created_at' => now(),
            ],
            [
                'id_guru' => 3,
                'nip' => '19800101003',
                'nama_guru' => 'Dewi Lestari, M.Pd',
                'no_hp' => '081234567893',
                'password' => Hash::make('guru123'),
                'created_at' => now(),
            ]
        ]);

        // 3. Mata Pelajaran
        DB::table('mata_pelajaran')->truncate();
        DB::table('mata_pelajaran')->insert([
            ['id_mapel' => 1, 'nama_mapel' => 'Matematika'],
            ['id_mapel' => 2, 'nama_mapel' => 'Bahasa Indonesia'],
            ['id_mapel' => 3, 'nama_mapel' => 'Ilmu Pengetahuan Alam (IPA)'],
            ['id_mapel' => 4, 'nama_mapel' => 'Pendidikan Pancasila'],
            ['id_mapel' => 5, 'nama_mapel' => 'Bahasa Inggris'],
        ]);

        // 4. Kelas
        DB::table('kelas')->truncate();
        DB::table('kelas')->insert([
            ['id_rooms' => 1, 'pararel' => 'Kelas 1A'],
            ['id_rooms' => 2, 'pararel' => 'Kelas 1B'],
            ['id_rooms' => 3, 'pararel' => 'Kelas 2A'],
            ['id_rooms' => 4, 'pararel' => 'Kelas 3A'],
        ]);

        // 5. Siswa
        DB::table('siswa')->truncate();
        DB::table('siswa')->insert([
            [
                'id_siswa' => 1,
                'id_mapel' => 1,
                'id_rooms' => 1,
                'nisn' => '0012345601',
                'nm_siswa' => 'Aditya Pratama',
                'no_hp' => '08111111111',
                'password' => Hash::make('siswa123'),
                'created_at' => now(),
            ],
            [
                'id_siswa' => 2,
                'id_mapel' => 1,
                'id_rooms' => 1,
                'nisn' => '0012345602',
                'nm_siswa' => 'Bella Safitri',
                'no_hp' => '08111111112',
                'password' => Hash::make('siswa123'),
                'created_at' => now(),
            ],
            [
                'id_siswa' => 3,
                'id_mapel' => 2,
                'id_rooms' => 1,
                'nisn' => '0012345603',
                'nm_siswa' => 'Candra Wijaya',
                'no_hp' => '08111111113',
                'password' => Hash::make('siswa123'),
                'created_at' => now(),
            ],
            [
                'id_siswa' => 4,
                'id_mapel' => 2,
                'id_rooms' => 2,
                'nisn' => '0012345604',
                'nm_siswa' => 'Dina Mariana',
                'no_hp' => '08111111114',
                'password' => Hash::make('siswa123'),
                'created_at' => now(),
            ],
            [
                'id_siswa' => 5,
                'id_mapel' => 3,
                'id_rooms' => 2,
                'nisn' => '0012345605',
                'nm_siswa' => 'Eko Prasetyo',
                'no_hp' => '08111111115',
                'password' => Hash::make('siswa123'),
                'created_at' => now(),
            ],
        ]);

        // 6. Barcode Siswa (untuk Scan QR Presensi)
        DB::table('barcode')->truncate();
        DB::table('barcode')->insert([
            ['id_barcode' => 1, 'id_siswa' => 1, 'kode_barcode' => 'QR-SISWA-001', 'created_at' => now()],
            ['id_barcode' => 2, 'id_siswa' => 2, 'kode_barcode' => 'QR-SISWA-002', 'created_at' => now()],
            ['id_barcode' => 3, 'id_siswa' => 3, 'kode_barcode' => 'QR-SISWA-003', 'created_at' => now()],
            ['id_barcode' => 4, 'id_siswa' => 4, 'kode_barcode' => 'QR-SISWA-004', 'created_at' => now()],
            ['id_barcode' => 5, 'id_siswa' => 5, 'kode_barcode' => 'QR-SISWA-005', 'created_at' => now()],
        ]);

        // 7. RPP Modul Ajar
        DB::table('rpp')->truncate();
        DB::table('rpp')->insert([
            [
                'id_rpp' => 1,
                'id_guru' => 1,
                'id_mapel' => 1,
                'id_rooms' => 1,
                'judul_rpp' => 'Modul Ajar Bilangan Cacah Sampai 100',
                'deskripsi' => 'Pengenalan nilai tempat dan penjumlahan sederhana fase A.',
                'komponen_checklist' => json_encode(['tujuan' => true, 'video' => true, 'kktp' => true, 'lkpd' => true]),
                'status' => 'terverifikasi',
                'catatan_revisi' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_rpp' => 2,
                'id_guru' => 2,
                'id_mapel' => 2,
                'id_rooms' => 1,
                'judul_rpp' => 'Membaca Nyaring & Menulis Cerita Bergambar',
                'deskripsi' => 'Materi literasi dasar untuk melatih kelancaran membaca siswa.',
                'komponen_checklist' => json_encode(['tujuan' => true, 'video' => true, 'kktp' => false, 'lkpd' => true]),
                'status' => 'menunggu_review',
                'catatan_revisi' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_rpp' => 3,
                'id_guru' => 3,
                'id_mapel' => 3,
                'id_rooms' => 3,
                'judul_rpp' => 'Ekosistem dan Rantai Makanan Dasar',
                'deskripsi' => 'Materi IPA kelas 2 tentang makhluk hidup dan lingkungannya.',
                'komponen_checklist' => json_encode(['tujuan' => true, 'video' => true, 'kktp' => false, 'lkpd' => true]),
                'status' => 'perlu_revisi',
                'catatan_revisi' => 'Mohon perbaikan KKTP dan penambahan video interaktif.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 8. Absen
        DB::table('absen')->truncate();
        DB::table('absen')->insert([
            [
                'id_absen' => 1,
                'id_guru' => 1,
                'id_siswa' => 1,
                'id_barcode' => 1,
                'metode' => 'scan_qr',
                'status' => 'Hadir',
                'keterangan' => 'Tepat Waktu',
                'waktu_absen' => now(),
                'tanggal' => now()->toDateString(),
            ],
            [
                'id_absen' => 2,
                'id_guru' => 1,
                'id_siswa' => 2,
                'id_barcode' => 2,
                'metode' => 'scan_qr',
                'status' => 'Hadir',
                'keterangan' => 'Tepat Waktu',
                'waktu_absen' => now(),
                'tanggal' => now()->toDateString(),
            ],
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
