<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RppSession extends Model
{
    protected $table = 'rpp_sessions';

    protected $primaryKey = 'id_session';

    protected $fillable = ['id_rpp', 'nomor_pertemuan', 'konten'];

    protected $casts = ['konten' => 'array'];

    public function rpp()
    {
        return $this->belongsTo(Rpp::class, 'id_rpp', 'id_rpp');
    }
}
