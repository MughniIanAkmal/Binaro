<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiSetting extends Model
{
    protected $table = 'absensi_settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getSettings(): \Illuminate\Support\Fluent
    {
        return new \Illuminate\Support\Fluent([
            'batas_awal' => static::get('batas_awal', '07:00'),
            'batas_tepat' => static::get('batas_tepat', '08:00'),
            'batas_tutup' => static::get('batas_tutup', '12:00'),
        ]);
    }
}