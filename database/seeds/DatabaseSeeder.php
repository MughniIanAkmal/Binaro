<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert sample data
        DB::table('mata_pelajaran')->insert([
            ['nama_mapel' => 'Matematika'],
            ['nama_mapel' => 'Bahasa Indonesia'],
            ['nama_mapel' => 'IPA'],
            ['nama_mapel' => 'IPS'],
            ['nama_mapel' => 'Bahasa Inggris'],
        ]);

        DB::table('kelas')->insert([
            ['pararel' => 'Kelas 1A'],
            ['pararel' => 'Kelas 1B'],
            ['pararel' => 'Kelas 2A'],
            ['pararel' => 'Kelas 2B'],
            ['pararel' => 'Kelas 3A'],
            ['pararel' => 'Kelas 3B'],
        ]);

        DB::table('guru')->insert([
            ['nip' => '19800101001', 'nama_guru' => 'Ahmad Fauzi, S.Kom', 'no_hp' => '081234567890'],
            ['nip' => '19800101002', 'nama_guru' => 'Siti Nurhaliza, S.Pd', 'no_hp' => '081234567891'],
            ['nip' => '19800101003', 'nama_guru' => 'Budi Santoso, S.Pd', 'no_hp' => '081234567892'],
        ]);

        DB::table('siswa')->insert([
            [
                'id_mapel' => 1,
                'id_rooms' => 1,
                'nisn' => '0012345678',
                'nm_siswa' => 'Andi Pratama',
                'no_hp' => '081234567893',
                'password' => Hash::make('password123')
            ],
            [
                'id_mapel' => 2,
                'id_rooms' => 2,
                'nisn' => '0012345679',
                'nm_siswa' => 'Budi Santoso',
                'no_hp' => '081234567894',
                'password' => Hash::make('password123')
            ],
        ]);

        DB::table('rpp')->insert([
            [
                'id_guru' => 1,
                'id_mapel' => 1,
                'id_rooms' => 1,
                'judul_rpp' => 'Matematika Dasar untuk Kelas 1',
                'deskripsi' => 'Modul pembelajaran matematika dasar untuk siswa kelas 1 SD',
                'komponen_checklist' => '["tujuan":true,"video":true,"kktp":true,"lkpd":true]',
                'status' => 'terverifikasi'
            ],
            [
                'id_guru' => 2,
                'id_mapel' => 2,
                'id_rooms' => 2,
                'judul_rpp' => 'Bahasa Indonesia untuk Kelas 2',
                'deskripsi' => 'Modul pembelajaran bahasa Indonesia untuk siswa kelas 2 SD',
                'komponen_checklist' => '["tujuan":true,"video":true,"kktp":false,"lkpd":true]',
                'status' => 'menunggu_review'
            ],
        ]);
    }
}
