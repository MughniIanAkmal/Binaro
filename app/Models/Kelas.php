<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_rooms';
    public $timestamps = false;
    protected $guarded = ['id_rooms'];
}