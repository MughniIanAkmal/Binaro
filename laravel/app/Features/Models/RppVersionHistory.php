<?php

namespace App\Features\Models;

use Illuminate\Database\Eloquent\Model;

class RppVersionHistory extends Model
{
    protected $table = 'rpp_version_history';

    protected $primaryKey = 'id_version';

    protected $fillable = ['id_rpp', 'versi', 'snapshot', 'perubahan_apa', 'dibuat_oleh'];

    protected $casts = ['snapshot' => 'array'];

    public function rpp()
    {
        return $this->belongsTo(Rpp::class, 'id_rpp', 'id_rpp');
    }
}
