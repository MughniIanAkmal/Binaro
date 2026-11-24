<?php

namespace App\Features\Models;

use Illuminate\Database\Eloquent\Model;

class CapaianPembelajaran extends Model
{
    protected $table = 'cp_master';

    protected $primaryKey = 'id_cp';

    protected $fillable = ['id_mapel', 'fase', 'elemen', 'cp_text', 'urutan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }
}
