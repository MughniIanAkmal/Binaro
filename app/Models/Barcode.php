<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Barcode extends Model
{
    protected $table = 'barcode';

    protected $primaryKey = 'id_barcode';

    public $timestamps = true;

    protected $fillable = ['id_siswa', 'kode_barcode', 'qr_image_path'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function buatKodeUnik(): string
    {
        do {
            $kode = 'QR-'.strtoupper(Str::random(8));
        } while (static::where('kode_barcode', $kode)->exists());

        return $kode;
    }

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
}
