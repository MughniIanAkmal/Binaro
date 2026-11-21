<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $primaryKey = 'id_rooms';

    public $timestamps = false;

    protected $fillable = [
        'pararel',
        'tingkat',
    ];

    protected $appends = ['nama_kelas', 'id_kelas'];

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'id_rooms', 'id_rooms');
    }

    /**
     * Fase Kurikulum Merdeka diturunkan dari tingkat kelas:
     * 1-2 = Fase A, 3-4 = Fase B, 5-6 = Fase C, 7-9 = D, 10 = E, 11-12 = F.
     * Null bila tingkat belum diketahui (data lama yang tidak bisa dibaca).
     */
    public function fase(): ?string
    {
        $tingkat = $this->tingkat;

        if (! $tingkat) {
            return null;
        }

        return match (true) {
            $tingkat <= 2 => 'Fase A',
            $tingkat <= 4 => 'Fase B',
            $tingkat <= 6 => 'Fase C',
            $tingkat <= 9 => 'Fase D',
            $tingkat === 10 => 'Fase E',
            default => 'Fase F',
        };
    }

    public function getNamaKelasAttribute()
    {
        return $this->pararel ?? $this->attributes['nama_kelas'] ?? '';
    }

    /** Akses praktis di Blade: {{ $kelas->fase }}. */
    public function getFaseAttribute(): ?string
    {
        return $this->fase();
    }

    public function getIdKelasAttribute()
    {
        return $this->id_rooms ?? $this->attributes['id_kelas'] ?? null;
    }
}
