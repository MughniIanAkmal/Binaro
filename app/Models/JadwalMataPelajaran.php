<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalMataPelajaran extends Model
{
    protected $table = 'jadwal_mata_pelajaran';
    protected $primaryKey = 'id_jadwal';
    public $timestamps = false;
    protected $guarded = ['id_jadwal'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }
}
