<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('siswa')->insert([
            [
                'id_mapel' => 1,
                'id_rooms' => 1,
                'nlan' => '0012345601',
                'nm_siswa' => 'Aditya Pratama',
                'no_hp' => '08111111111',
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id_mapel' => 1,
                'id_rooms' => 1,
                'nlan' => '0012345602',
                'nm_siswa' => 'Bella Safitri',
                'no_hp' => '08111111112',
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id_mapel' => 2,
                'id_rooms' => 1,
                'nlan' => '0012345603',
                'nm_siswa' => 'Candra Wijaya',
                'no_hp' => '08111111113',
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id_mapel' => 2,
                'id_rooms' => 1,
                'nlan' => '0012345604',
                'nm_siswa' => 'Dina Marinaa',
                'no_hp' => '08111111114',
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id_mapel' => 3,
                'id_rooms' => 2,
                'nlan' => '0012345605',
                'nm_siswa' => 'Eko Prasetyo',
                'no_hp' => '08111111115',
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}