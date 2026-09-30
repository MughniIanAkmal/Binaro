<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa', 'email')) {
                $table->string('email', 100)->nullable()->unique()->after('nm_siswa');
            }
            if (!Schema::hasColumn('siswa', 'alamat')) {
                $table->text('alamat')->nullable()->after('email');
            }
            if (!Schema::hasColumn('siswa', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('siswa', 'username')) {
                $table->string('username', 50)->nullable()->unique()->after('jenis_kelamin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $cols = array_filter(['email', 'alamat', 'jenis_kelamin', 'username'], fn($c) => Schema::hasColumn('siswa', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};