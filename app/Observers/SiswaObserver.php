<?php

namespace App\Observers;

use App\Models\Barcode;
use App\Models\Siswa;
use Illuminate\Support\Str;

class SiswaObserver
{
    /**
     * Handle the Siswa "created" event.
     *
     * Generates a unique barcode record for every newly created Siswa.
     */
    public function created(Siswa $siswa): void
    {
        Barcode::create([
            'id_siswa' => $siswa->id_siswa,
            'kode_barcode' => 'BIN-' . $siswa->nisn . '-' . Str::upper(Str::random(6)),
            'is_active' => true,
        ]);
    }
}