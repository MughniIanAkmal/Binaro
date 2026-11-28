<?php

namespace App\Features\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $primaryKey = 'id_mapel';

    public $timestamps = false;

    protected $guarded = ['id_mapel'];

    /**
     * Kelas yang menjadi tujuan mapel ini (1 mapel = 1 kelas).
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }

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

    /**
     * Guru yang diizinkan admin mengajar mapel ini (pivot guru_mapel).
     * Namanya jamak (gurus) karena belongsToMany mengembalikan Collection —
     * jangan dinamai guru(), karena view siswa memakai ->guru->nama_guru
     * dengan asumsi relasi tunggal dan akan 500 kalau dapat Collection.
     */
    public function gurus()
    {
        // Tanpa withTimestamps(): alasannya sama seperti di Guru::mapel() —
        // tabel guru_mapel lama bisa tidak punya created_at/updated_at.
        return $this->belongsToMany(Guru::class, 'guru_mapel', 'id_mapel', 'id_guru');
    }
}
