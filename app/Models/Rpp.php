<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rpp extends Model
{
    protected $table = 'rpp';
    protected $primaryKey = 'id_rpp';
    public $timestamps = true;

    protected $fillable = ['id_guru', 'id_rooms', 'id_mapel', 'judul_rpp', 'deskripsi', 'komponen_checklist', 'status', 'file_rpp'];

    protected $casts = [
        'komponen_checklist' => 'array',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }
}