<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    protected $table = 'absen';
    protected $primaryKey = 'id_absen';

    public $timestamps = false;

    protected $fillable = [
        'id_guru', 'id_siswa', 'id_barcode', 'status', 'waktu_absen',
    ];

    protected $casts = [
        'waktu_absen' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}
