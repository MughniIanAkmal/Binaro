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
        'id_kelas',
        'nisn',
        'nama',
        'password',
        'foto',
    ];

    protected $appends = ['nama_siswa', 'nisn', 'nama_kelas'];

    public function barcode()
    {
        return $this->hasOne(Barcode::class, 'id_siswa', 'id_siswa');
    }

    public function absens()
    {
        return $this->hasMany(Absen::class, 'id_siswa', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function getNamaSiswaAttribute()
    {
        return $this->nama;
    }

    public function getNisnAttribute()
    {
        return $this->attributes['nisn'] ?? null;
    }

    public function getNamaKelasAttribute()
    {
        return $this->kelas?->nama_kelas;
    }
}
