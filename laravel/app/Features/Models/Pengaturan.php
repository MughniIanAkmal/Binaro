<?php

namespace App\Features\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Ambil nilai pengaturan global sekolah berdasarkan kunci.
     */
    public static function ambil(string $kunci, $default = null)
    {
        $row = static::where('kunci', $kunci)->first();

        return $row?->nilai ?? $default;
    }

    /**
     * Simpan (buat atau perbarui) nilai pengaturan global sekolah.
     */
    public static function simpan(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => (string) $nilai]);
    }
}
