<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal_mata_pelajaran';

    protected $primaryKey = 'id_jadwal';

    public $timestamps = false;


    protected $fillable = [
        'id_mapel',
        'id_guru',
        'id_rooms',
        'hari',
        'jam',
    ];
}
