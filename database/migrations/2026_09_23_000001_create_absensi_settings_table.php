<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('absensi_settings')->insert([
            ['key' => 'batas_awal', 'value' => '07:00'],
            ['key' => 'batas_tepat', 'value' => '08:00'],
            ['key' => 'batas_tutup', 'value' => '12:00'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_settings');
    }
};