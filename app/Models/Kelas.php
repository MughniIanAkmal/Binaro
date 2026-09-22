<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_rooms';

    public $timestamps = false;

    protected $fillable = [
        'pararel',
    ];

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'id_rooms', 'id_rooms');
    }

    public function getNamaKelasAttribute()
    {
        return $this->pararel;
    }
}
