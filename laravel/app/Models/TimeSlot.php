<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $table = 'time_slots';

    protected $primaryKey = 'id_slot';

    protected $fillable = [
        'nama',
        'jam_mulai',
        'jam_selesai',
        'urutan',
        'is_break',
    ];

    protected $casts = [
        'is_break' => 'boolean',
    ];

    /**
     * Label rentang waktu siap tampil, mis. "07:00 - 08:00".
     */
    public function getRentangAttribute(): string
    {
        return $this->jam_mulai.' - '.$this->jam_selesai;
    }

    /**
     * Format yang dipakai kolom `jam` pada tabel jadwal, mis. "07.00-08.00".
     */
    public function getJamTeksAttribute(): string
    {
        return str_replace(':', '.', $this->jam_mulai).'-'.str_replace(':', '.', $this->jam_selesai);
    }

    public function jadwals()
    {
        return $this->hasMany(JadwalMataPelajaran::class, 'time_slot_id', 'id_slot');
    }
}
