<?php

namespace App\Features\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalMataPelajaran extends Model
{
    protected $table = 'jadwal_mata_pelajaran';

    protected $primaryKey = 'id_jadwal';

    public $timestamps = false;

    protected $guarded = ['id_jadwal'];

    protected $appends = ['jam_rentang'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function mapel()
    {
        return $this->mataPelajaran();
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id', 'id_slot');
    }

    /**
     * Rentang jam siap tampil. Utamakan slot; fallback ke kolom `jam` lama
     * agar data lama (yang belum punya time_slot_id) tetap tampil.
     */
    public function getJamRentangAttribute(): string
    {
        if ($this->timeSlot) {
            return $this->timeSlot->jam_mulai.' - '.$this->timeSlot->jam_selesai;
        }

        return (string) ($this->attributes['jam'] ?? '-');
    }
}
