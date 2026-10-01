<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pr extends Model
{
    protected $table = 'pr';
    protected $primaryKey = 'id_pr';
    protected $guarded = ['id_pr'];

    protected $casts = [
        'tgl_tenggat' => 'datetime',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'id_pr', 'id_pr');
    }
}