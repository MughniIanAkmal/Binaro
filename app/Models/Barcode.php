<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    protected $table = 'barcode';
    protected $primaryKey = 'id_barcode';

    public $timestamps = true;

    protected $fillable = ['id_siswa', 'kode_barcode'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function getKodeAttribute()
    {
        return $this->kode_barcode;
    }

    public function setKodeAttribute($value)
    {
        $this->attributes['kode_barcode'] = $value;
    }

    /**
     * Kode unik, contoh: SD-K7M2XQ9PTA
     * Huruf/angka yang mirip (0/O, 1/I) sengaja dibuang agar mudah diketik manual.
     */
    public static function buatKodeUnik(): string
    {
        $alfabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $kode = 'SD-';
            for ($i = 0; $i < 10; $i++) {
                $kode .= $alfabet[random_int(0, strlen($alfabet) - 1)];
            }
        } while (static::where('kode_barcode', $kode)->exists());

        return $kode;
    }
}
