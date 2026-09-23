<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    protected $table = 'absen';
    protected $primaryKey = 'id_absen';

    public $timestamps = false;

    protected $fillable = [
        'id_guru',
        'id_siswa',
        'id_barcode',
        'metode',
        'status',
        'keterangan',
        'berkas_surat',
        'waktu_absen',
        'tanggal',
    ];

    protected $casts = [
        'waktu_absen' => 'datetime',
        'tanggal' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function barcode()
    {
        return $this->belongsTo(Barcode::class, 'id_barcode', 'id_barcode');
    }
}
