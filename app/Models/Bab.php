<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bab extends Model
{
    protected $table = 'bab';
    protected $primaryKey = 'id_bab';
    protected $guarded = ['id_bab'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function subBab()
    {
        return $this->hasMany(SubBab::class, 'id_bab', 'id_bab');
    }
}