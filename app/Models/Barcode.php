<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    protected $table = 'barcode';
    protected $primaryKey = 'id_barcode';
    protected $guarded = ['id_barcode'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}