<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoalQuiz extends Model
{
    protected $table = 'soal_quiz';
    protected $primaryKey = 'id_soal';
    protected $guarded = ['id_soal'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'id_quiz', 'id_quiz');
    }
}
