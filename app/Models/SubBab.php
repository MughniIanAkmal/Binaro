<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubBab extends Model
{
    protected $table = 'sub_bab';
    protected $primaryKey = 'id_sub_bab';
    protected $guarded = ['id_sub_bab'];

    public function bab()
    {
        return $this->belongsTo(Bab::class, 'id_bab', 'id_bab');
    }

    public function materi()
    {
        return $this->hasMany(Materi::class, 'id_sub_bab', 'id_sub_bab');
    }
}