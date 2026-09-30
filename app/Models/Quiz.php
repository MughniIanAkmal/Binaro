<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quiz';
    protected $primaryKey = 'id_quiz';
    protected $guarded = ['id_quiz'];

    protected static function booted()
    {
        static::saving(function ($quiz) {
            if (!$quiz->id_mapel && $quiz->id_sub_bab) {
                $subBab = SubBab::with('bab')->find($quiz->id_sub_bab);
                if ($subBab && $subBab->bab) {
                    $quiz->id_mapel = $subBab->bab->id_mapel;
                }
            }
            if (!$quiz->nama_quiz && !empty($quiz->judul_quiz)) {
                $quiz->nama_quiz = $quiz->judul_quiz;
            }
            if (!$quiz->judul_quiz && !empty($quiz->nama_quiz)) {
                $quiz->judul_quiz = $quiz->nama_quiz;
            }
        });
    }

    public function getJudulQuizAttribute($value)
    {
        return $value ?: ($this->attributes['nama_quiz'] ?? '');
    }

    public function setJudulQuizAttribute($value)
    {
        $this->attributes['judul_quiz'] = $value;
        $this->attributes['nama_quiz'] = $value;
    }

    public function getNamaQuizAttribute($value)
    {
        return $value ?: ($this->attributes['judul_quiz'] ?? '');
    }

    public function setNamaQuizAttribute($value)
    {
        $this->attributes['nama_quiz'] = $value;
        $this->attributes['judul_quiz'] = $value;
    }

    public function subBab()
    {
        return $this->belongsTo(SubBab::class, 'id_sub_bab', 'id_sub_bab');
    }

    public function soal()
    {
        return $this->hasMany(SoalQuiz::class, 'id_quiz', 'id_quiz');
    }

    public function hasilSiswa()
    {
        return $this->hasMany(HasilKuisSiswa::class, 'id_quiz', 'id_quiz');
    }

    public function hasil()
    {
        return $this->hasMany(HasilKuisSiswa::class, 'id_quiz', 'id_quiz');
    }

    public function materi()
    {
        return $this->hasOne(Materi::class, 'id_quiz', 'id_quiz');
    }
}
