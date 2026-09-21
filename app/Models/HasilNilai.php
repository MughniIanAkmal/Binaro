<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilNilai extends Model
{
    protected $table = 'hasil_nilai';
    protected $primaryKey = 'id_hasil_nilai';
    public $timestamps = false;
    protected $guarded = ['id_hasil_nilai'];

    public function nilai()
    {
        return $this->belongsTo(Nilai::class, 'id_nilai', 'id_nilai');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}