<?php

namespace App\Observers;

use App\Models\Barcode;
use App\Models\Siswa;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiswaObserver
{
    /**
     * Handle the Siswa "created" event.
     *
     * Generates a unique barcode record for every newly created Siswa.
     * Kolom is_active hanya diisi jika ada di skema (skema produksi lama
     * tidak punya kolom ini, skema tes baru punya).
     */
    public function created(Siswa $siswa): void
    {
        if (Barcode::where('id_siswa', $siswa->id_siswa)->exists()) {
            return;
        }

        $data = [
            'id_siswa' => $siswa->id_siswa,
            'kode_barcode' => 'BIN-' . $siswa->nisn . '-' . Str::upper(Str::random(6)),
        ];

        if (Schema::hasColumn('barcode', 'is_active')) {
            $data['is_active'] = true;
        }

        Barcode::create($data);
    }
}