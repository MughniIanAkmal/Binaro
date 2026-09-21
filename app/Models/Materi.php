<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materi';
    protected $primaryKey = 'id_materi';
    protected $guarded = ['id_materi'];

    public function subBab()
    {
        return $this->belongsTo(SubBab::class, 'id_sub_bab', 'id_sub_bab');
    }

    public function bab()
    {
        return $this->belongsTo(Bab::class, 'id_bab', 'id_bab');
    }
}