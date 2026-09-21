<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';
    protected $primaryKey = 'id_guru';
    protected $guarded = ['id_guru'];

    public function rpp()
    {
        return $this->hasMany(Rpp::class, 'id_guru', 'id_guru');
    }
}