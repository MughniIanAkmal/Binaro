<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelas4A = Kelas::firstOrCreate(['pararel' => 'Kelas 4A']);
        $kelas5A = Kelas::firstOrCreate(['pararel' => 'Kelas 5A']);
        $kelas4B = Kelas::firstOrCreate(['pararel' => 'Kelas 4B']);
        $mapelMat = MataPelajaran::firstOrCreate(['nama_mapel' => 'Matematika']);
        $mapelBIndo = MataPelajaran::firstOrCreate(['nama_mapel' => 'Bahasa Indonesia']);

        $siswas = [
            [
                'id_mapel' => $mapelMat->id_mapel,
                'id_rooms' => $kelas4A->id_rooms,
                'nisn' => '0012345601',
                'nm_siswa' => 'Aditya Pratama',
                'no_hp' => '08111111111',
                'email' => 'aditya123@gmail.com',
                'alamat' => null,
                'jenis_kelamin' => 'L',
                'username' => 'ADIT',
                'password' => 'siswa1234',
            ],
            [
                'id_mapel' => $mapelMat->id_mapel,
                'id_rooms' => $kelas4A->id_rooms,
                'nisn' => '0012345602',
                'nm_siswa' => 'Bella Safitri',
                'no_hp' => '08111111112',
                'email' => null,
                'alamat' => null,
                'jenis_kelamin' => 'P',
                'username' => null,
                'password' => '$2y$12$2u4GwxrHc2aGiPSX.6SQQ./YWEWIE8/lWNJmj7h6FBP6yODKOnxmm',
            ],
            [
                'id_mapel' => null,
                'id_rooms' => $kelas4A->id_rooms,
                'nisn' => '0012345603',
                'nm_siswa' => 'Candra Wijaya',
                'no_hp' => '08111111113',
                'email' => null,
                'alamat' => null,
                'jenis_kelamin' => 'L',
                'username' => null,
                'password' => '$2y$12$bYLvx6rLyTNJ1IZaE7IupuHch./40YsS0hWxEWyWv9GBSyjQjZf66',
            ],
            [
                'id_mapel' => null,
                'id_rooms' => $kelas5A->id_rooms,
                'nisn' => '0012345604',
                'nm_siswa' => 'Dina Mariana',
                'no_hp' => '08111111114',
                'email' => null,
                'alamat' => null,
                'jenis_kelamin' => 'P',
                'username' => null,
                'password' => '$2y$12$XDTo3in9iOQd4MVy7x0rFOzzSZNc0aGDyY78Zm6qH62DqHKfjtkc.',
            ],
            [
                'id_mapel' => $mapelBIndo->id_mapel,
                'id_rooms' => $kelas5A->id_rooms,
                'nisn' => '0012345605',
                'nm_siswa' => 'Eko Prasetyo',
                'no_hp' => '08111111115',
                'email' => null,
                'alamat' => null,
                'jenis_kelamin' => 'L',
                'username' => null,
                'password' => '$2y$12$ZHnL514lQ0cJZ0q71rfEvOBADEYoQfiwqpKgiTm9jbT78xEPgJJXK',
            ],
            [
                'id_mapel' => $mapelMat->id_mapel,
                'id_rooms' => $kelas4B->id_rooms,
                'nisn' => '0012345678',
                'nm_siswa' => 'Budi Santoso',
                'no_hp' => '089876543210',
                'email' => 'budi.santoso@siswa.kalitapen01.sch.id',
                'alamat' => 'Jl. Merdeka No. 45',
                'jenis_kelamin' => 'L',
                'username' => 'budisiswa',
                'password' => 'siswa123',
            ],
        ];

        foreach ($siswas as $data) {
            Siswa::updateOrCreate(
                ['nisn' => $data['nisn']],
                $data
            );
        }
    }
}