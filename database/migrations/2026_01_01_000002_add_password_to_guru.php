<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('password', 255)->nullable()->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};