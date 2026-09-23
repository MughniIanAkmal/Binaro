<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';
    protected $primaryKey = 'id_guru';
    protected $guarded = ['id_guru'];

    protected $appends = ['nama'];

    public function getNamaAttribute()
    {
        return $this->attributes['nama_guru'] ?? null;
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama_guru'] = $value;
    }

    public function rpp()
    {
        return $this->hasMany(Rpp::class, 'id_guru', 'id_guru');
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalMataPelajaran::class, 'id_guru', 'id_guru');
    }
}