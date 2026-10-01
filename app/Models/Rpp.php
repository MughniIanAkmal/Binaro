<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rpp extends Model
{
    protected $table = 'rpp';
    protected $primaryKey = 'id_rpp';
    public $timestamps = true;

    protected $fillable = [
        'id_guru',
        'id_rooms',
        'id_jadwal',
        'id_mapel',
        'judul_rpp',
        'deskripsi',
        'alokasi_waktu',
        'fase',
        'modul_ke',
        'target_jadwal',
        'ruang',
        'target_tanggal',
        'komponen_checklist',
        'status',
        'catatan_revisi',
        'file_rpp',
    ];

    protected $casts = [
        'komponen_checklist' => 'array',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalMataPelajaran::class, 'id_jadwal', 'id_jadwal');
    }
}
