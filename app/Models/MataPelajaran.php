<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    protected $primaryKey = 'id_mapel';
    public $timestamps = false;
    protected $guarded = ['id_mapel'];

    public function bab()
    {
        return $this->hasMany(Bab::class, 'id_mapel', 'id_mapel');
    }

    public function rpps()
    {
        return $this->hasMany(Rpp::class, 'id_mapel', 'id_mapel');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'id_mapel', 'id_mapel');
    }
}
