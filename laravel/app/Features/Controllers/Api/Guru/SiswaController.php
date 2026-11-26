<?php

namespace App\Features\Controllers\Api\Guru;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Guru;
use App\Features\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends ApiController
{
    protected function authGuruId(Request $request): ?int
    {
        $user = $request->user();
        if ($user && isset($user->id_guru)) {
            return (int) $user->id_guru;
        }

        $id = $request->input('id_guru')
            ?? $request->query('id_guru')
            ?? session()->get('guru_id')
            ?? session()->get('user_id');

        if (! $id) {
            $firstGuru = Guru::first();

            return $firstGuru?->id_guru;
        }

        return (int) $id;
    }

    public function listSiswa(Request $request)
    {
        $this->authGuruId($request);
        $siswa = Siswa::with(['kelas', 'barcode'])->get();

        return $this->success($siswa, 'Data siswa berhasil dimuat');
    }
}
