<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
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