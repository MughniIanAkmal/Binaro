<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
        'id_bab',
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
        'alokasi_waktu_menit',
        'total_pertemuan',
        'semester',
        'tahun_ajaran',
        'nomor_rpp',
    ];

    protected $casts = [
        'komponen_checklist' => 'array',
        'alokasi_waktu_menit' => 'integer',
        'total_pertemuan' => 'integer',
    ];

    /**
     * Komponen yang wajib ada sebelum RPP boleh diajukan (hard-block).
     * Turunan Permendikdasmen 1/2026 Pasal 5 & 8: tujuan, langkah, asesmen.
     */
    public const KOMPONEN_WAJIB = [
        'tujuan' => 'Tujuan pembelajaran',
        'langkah' => 'Langkah pembelajaran (memahami, mengaplikasi, merefleksi)',
        'asesmen' => 'Asesmen (minimal dua teknik berbeda)',
    ];

    /**
     * Komponen pendukung: hanya memengaruhi skor kelengkapan, tidak memblokir
     * pengajuan (rujukan Panduan Pembelajaran dan Asesmen, bukan pasal).
     */
    public const KOMPONEN_DISARANKAN = [
        'media' => 'Media pembelajaran',
        'lkpd' => 'LKPD',
        'refleksi' => 'Refleksi peserta didik',
        'pengayaan' => 'Pengayaan',
        'remedial' => 'Remedial',
        'glosarium' => 'Glosarium & daftar pustaka',
        'video' => 'Video / bahan tayang',
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

    public function bab()
    {
        return $this->belongsTo(Bab::class, 'id_bab', 'id_bab');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalMataPelajaran::class, 'id_jadwal', 'id_jadwal');
    }

    public function catatan()
    {
        return $this->hasMany(RppCatatan::class, 'id_rpp', 'id_rpp')->latest('id_catatan');
    }

    public function sessions()
    {
        return $this->hasMany(RppSession::class, 'id_rpp', 'id_rpp')->orderBy('nomor_pertemuan');
    }

    public function versions()
    {
        return $this->hasMany(RppVersionHistory::class, 'id_rpp', 'id_rpp')->orderByDesc('versi');
    }

    /** @return Collection<int, string> daftar kunci komponen wajib yang belum terpenuhi */
    public function komponenWajibKurang(): Collection
    {
        $checklist = (array) ($this->komponen_checklist ?? []);

        return collect(self::KOMPONEN_WAJIB)
            ->keys()
            ->reject(fn ($kunci) => (bool) ($checklist[$kunci] ?? false))
            ->values();
    }

    public function komponenWajibLengkap(): bool
    {
        return $this->komponenWajibKurang()->isEmpty();
    }

    public function jumlahDisarankanTerisi(): int
    {
        $checklist = (array) ($this->komponen_checklist ?? []);

        return collect(self::KOMPONEN_DISARANKAN)
            ->keys()
            ->filter(fn ($kunci) => (bool) ($checklist[$kunci] ?? false))
            ->count();
    }

    /** Skor 0-100: komponen wajib 70%, komponen disarankan 30%. */
    public function skorKelengkapan(): int
    {
        $wajibTotal = count(self::KOMPONEN_WAJIB);
        $wajibTerisi = $wajibTotal - $this->komponenWajibKurang()->count();
        $disarankanTotal = count(self::KOMPONEN_DISARANKAN);

        $skorWajib = $wajibTotal > 0 ? ($wajibTerisi / $wajibTotal) * 70 : 70;
        $skorDisarankan = $disarankanTotal > 0 ? ($this->jumlahDisarankanTerisi() / $disarankanTotal) * 30 : 30;

        return (int) round($skorWajib + $skorDisarankan);
    }
}
