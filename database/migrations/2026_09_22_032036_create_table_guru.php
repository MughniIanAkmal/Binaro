<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (!Schema::hasColumn('guru', 'email')) {
                $table->string('email')->nullable()->unique()->after('no_hp');
            }
            if (!Schema::hasColumn('guru', 'alamat')) {
                $table->text('alamat')->nullable()->after('email');
            }
            if (!Schema::hasColumn('guru', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('guru', 'username')) {
                $table->string('username')->nullable()->unique()->after('jenis_kelamin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $cols = array_filter(['email', 'alamat', 'jenis_kelamin', 'username'], fn($c) => Schema::hasColumn('guru', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
