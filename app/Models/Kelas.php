<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';

    public $timestamps = true;

    protected $fillable = [
        'nama_kelas',
    ];

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'id_kelas', 'id_kelas');
    }
}
