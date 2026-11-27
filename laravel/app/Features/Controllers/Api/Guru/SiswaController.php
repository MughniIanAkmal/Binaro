<?php

namespace App\Features\Controllers\Api\Guru;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Guru;
use App\Features\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends ApiController
{
    public function listSiswa(Request $request)
    {
        if (! $this->authGuruId($request)) {
            return $this->error('User tidak terautentikasi', 401);
        }
        $siswa = Siswa::with(['kelas', 'barcode'])->get();

        return $this->success($siswa, 'Data siswa berhasil dimuat');
    }
}
