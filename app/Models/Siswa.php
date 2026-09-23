<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';

    public $timestamps = true;

    protected $fillable = [
        'id_mapel',
        'id_rooms',
        'nisn',
        'nm_siswa',
        'nama',
        'nis',
        'password',
        'no_hp',
        'email',
        'alamat',
        'jenis_kelamin',
        'username',
    ];

    protected $appends = ['nama_siswa', 'nama', 'nis', 'nisn', 'nama_kelas'];

    public function barcode()
    {
        return $this->hasOne(Barcode::class, 'id_siswa', 'id_siswa');
    }

    public function absens()
    {
        return $this->hasMany(Absen::class, 'id_siswa', 'id_siswa');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }

    public function getNamaSiswaAttribute()
    {
        return $this->attributes['nm_siswa'] ?? null;
    }

    public function getNamaAttribute()
    {
        return $this->attributes['nm_siswa'] ?? null;
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nm_siswa'] = $value;
    }

    public function getNisnAttribute()
    {
        return $this->attributes['nisn'] ?? null;
    }

    public function getNisAttribute()
    {
        return $this->attributes['nisn'] ?? null;
    }

    public function setNisAttribute($value)
    {
        $this->attributes['nisn'] = $value;
    }

    public function getNamaKelasAttribute()
    {
        return $this->kelas?->pararel ?? $this->kelas?->nama_kelas ?? '-';
    }
}
