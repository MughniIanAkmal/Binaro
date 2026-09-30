<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilKuisSiswa extends Model
{
    protected $table = 'hasil_kuis_siswa';
    protected $primaryKey = 'id_hasil';
    protected $guarded = ['id_hasil'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'id_quiz', 'id_quiz');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}
