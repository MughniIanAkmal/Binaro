<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rpp extends Model
{
    protected $table = 'rpp';
    protected $primaryKey = 'id_rpp';
    protected $guarded = ['id_rpp'];

    protected $casts = [
        'komponen_checklist' => 'array',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }
}
