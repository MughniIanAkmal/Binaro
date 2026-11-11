<?php

namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        Guru::create([
            'nip' => '19880101',
            'nama_guru' => 'Ibu Sarah Wijaya, S.Pd.',
            'no_hp' => '08123456789',
            'password' => Hash::make('password123'),
        ]);
    }
}
