<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';

    protected $primaryKey = 'id_guru';

    public $timestamps = false;

    protected $fillable = [
        'nip',
        'nama_guru',
        'no_hp',
    ];
}
