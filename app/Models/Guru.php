<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Guru extends Authenticatable
{
    protected $table = 'guru';
    protected $primaryKey = 'id_guru';
    public $timestamps = true;

    protected $fillable = [
        'nip',
        'nama_guru',
        'no_hp',
        'email',
        'alamat',
        'jenis_kelamin',
        'username',
        'password',
    ];

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

    public function jadwalMataPelajaran()
    {
        return $this->hasMany(JadwalMataPelajaran::class, 'id_guru', 'id_guru');
    }

    public function absens()
    {
        return $this->hasMany(Absen::class, 'id_guru', 'id_guru');
    }

    public function pr()
    {
        return $this->hasMany(Pr::class, 'id_guru', 'id_guru');
    }

    public function quiz()
    {
        return $this->hasMany(Quiz::class, 'id_guru', 'id_guru');
    }
}