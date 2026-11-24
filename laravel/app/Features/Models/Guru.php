<?php

namespace App\Features\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use \Laravel\Sanctum\HasApiTokens;

class Guru extends Authenticatable
{
    use HasApiTokens;
    protected $table = 'guru';

    protected $primaryKey = 'id_guru';

    public $timestamps = true;

    protected $hidden = ['password'];
    protected $fillable = [
        'nip',
        'nama_guru',
        'no_hp',
        'email',
        'alamat',
        'jenis_kelamin',
        'username',
        'password',
        // Revisi: lock guru ke 1 kelas (diatur admin, nullable = belum di-lock).
        'id_rooms',
    ];

    protected $appends = ['nama'];

    public function getNamaAttribute()
    {
        return $this->attributes['nama_guru'] ?? null;
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama_guru'] = $value;
    }

    /** Kelas yang di-lock untuk guru ini (1 guru = 1 kelas, diatur admin). */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_rooms', 'id_rooms');
    }

    /** Mapel yang diizinkan admin untuk guru ini (pivot guru_mapel). */
    public function mapel()
    {
        // Tanpa withTimestamps(): tidak ada kode yang membaca pivot
        // created_at/updated_at, dan tabel guru_mapel di database lama
        // bisa saja sudah ada tanpa kedua kolom itu (migrasi hanya
        // membuat tabel kalau belum ada) — withTimestamps() bikin query
        // ikut select kedua kolom itu dan halaman daftar guru jadi 500.
        return $this->belongsToMany(MataPelajaran::class, 'guru_mapel', 'id_guru', 'id_mapel');
    }

    /** null = belum di-lock admin, semua alur lama tetap jalan. */
    public function lockedKelasId(): ?int
    {
        return $this->id_rooms ? (int) $this->id_rooms : null;
    }

    public function isLockedToKelas(?int $idRooms): bool
    {
        $locked = $this->lockedKelasId();

        return $locked !== null && $idRooms !== null && $locked === $idRooms;
    }

    /**
     * Mapel yang boleh diajar guru ini.
     * Kalau admin sudah mengisi pivot guru_mapel, pivot itu yang berlaku.
     * Kalau belum pernah diisi, fallback ke mapel dari jadwal mengajar
     * (perilaku lama) supaya guru lama tidak tiba-tiba kehilangan akses.
     */
    public function taughtMapelIds(): \Illuminate\Support\Collection
    {
        $pivotIds = $this->mapel()->pluck('mata_pelajaran.id_mapel')->map(fn ($id) => (int) $id);

        if ($pivotIds->isNotEmpty()) {
            return $pivotIds->values();
        }

        return JadwalMataPelajaran::where('id_guru', $this->id_guru)
            ->distinct()->pluck('id_mapel')->map(fn ($id) => (int) $id)->values();
    }

    public function canTeachMapel(?int $idMapel): bool
    {
        if ($idMapel === null) {
            return false;
        }

        return $this->taughtMapelIds()->contains($idMapel);
    }

    /** true kalau admin sudah pernah mengisi pivot guru_mapel untuk guru ini. */
    public function isMapelCustomized(): bool
    {
        return $this->mapel()->exists();
    }

    public function rpp()
    {
        return $this->hasMany(Rpp::class, 'id_guru', 'id_guru');
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalMataPelajaran::class, 'id_guru', 'id_guru');
    }

    public function jadwalMataPelajaran()
    {
        return $this->hasMany(JadwalMataPelajaran::class, 'id_guru', 'id_guru');
    }

    public function absens()
    {
        return $this->hasMany(Absen::class, 'id_guru', 'id_guru');
    }

    public function pr()
    {
        return $this->hasMany(Pr::class, 'id_guru', 'id_guru');
    }

    public function quiz()
    {
        return $this->hasMany(Quiz::class, 'id_guru', 'id_guru');
    }
}
