<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = Kelas::firstOrCreate(['pararel' => 'Kelas 4A']);
        $mapel = MataPelajaran::firstOrCreate(['nama_mapel' => 'Matematika']);

        $siswas = [
            [
                'nisn' => '0012345601',
                'nm_siswa' => 'Aditya Pratama',
                'no_hp' => '08111111111',
            ],
            [
                'nisn' => '0012345602',
                'nm_siswa' => 'Bella Safitri',
                'no_hp' => '08111111112',
            ],
            [
                'nisn' => '0012345603',
                'nm_siswa' => 'Candra Wijaya',
                'no_hp' => '08111111113',
            ],
            [
                'nisn' => '0012345604',
                'nm_siswa' => 'Dina Marinaa',
                'no_hp' => '08111111114',
            ],
            [
                'nisn' => '0012345605',
                'nm_siswa' => 'Eko Prasetyo',
                'no_hp' => '08111111115',
            ],
        ];

        foreach ($siswas as $data) {
            Siswa::updateOrCreate(
                ['nisn' => $data['nisn']],
                [
                    'id_mapel' => $mapel->id_mapel,
                    'id_rooms' => $kelas->id_rooms,
                    'nm_siswa' => $data['nm_siswa'],
                    'no_hp' => $data['no_hp'],
                    'password' => Hash::make('123456'),
                ]
            );
        }
    }
}