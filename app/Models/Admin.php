<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    protected $guarded = ['id_admin'];

    public function getNamaAdminAttribute()
    {
        return $this->attributes['nama_admin'] ?? $this->attributes['nama'] ?? 'Administrator';
    }

    public function getNamaAttribute()
    {
        return $this->attributes['nama_admin'] ?? $this->attributes['nama'] ?? 'Administrator';
    }
}