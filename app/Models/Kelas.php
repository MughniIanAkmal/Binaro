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

    protected $appends = ['nama_kelas', 'id_kelas'];

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'id_rooms', 'id_rooms');
    }

    public function getNamaKelasAttribute()
    {
        return $this->pararel ?? $this->attributes['nama_kelas'] ?? '';
    }

    public function getIdKelasAttribute()
    {
        return $this->id_rooms ?? $this->attributes['id_kelas'] ?? null;
    }
}
