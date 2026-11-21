<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RppCatatan extends Model
{
    protected $table = 'rpp_catatan';

    protected $primaryKey = 'id_catatan';

    protected $fillable = [
        'id_rpp',
        'penulis_role',
        'penulis_id',
        'penulis_nama',
        'status_saat_itu',
        'isi',
    ];

    public function rpp()
    {
        return $this->belongsTo(Rpp::class, 'id_rpp', 'id_rpp');
    }

    public function getInisialAttribute(): string
    {
        $nama = trim((string) ($this->penulis_nama ?? ''));

        if ($nama === '') {
            return strtoupper(substr((string) $this->penulis_role, 0, 2));
        }

        $kata = preg_split('/\s+/', $nama) ?: [];
        $inisial = '';
        foreach (array_slice($kata, 0, 2) as $k) {
            $inisial .= strtoupper(substr($k, 0, 1));
        }

        return $inisial !== '' ? $inisial : 'US';
    }
}
