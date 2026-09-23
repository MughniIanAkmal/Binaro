<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Guru;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeedAuthData extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'id_admin' => 1,
            'nip' => '0001000',
            'nama_admin' => 'Super Admin',
            'password' => Hash::make('admin123'),
        ]);

        Guru::create([
            'id_guru' => 1,
            'nip' => '0002000',
            'nama_guru' => 'Guru Utama',
            'nuptk' => '1234567890',
            'password' => Hash::make('guru123'),
        ]);
    }
}