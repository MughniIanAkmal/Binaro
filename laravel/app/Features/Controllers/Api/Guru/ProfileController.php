<?php

namespace App\Features\Controllers\Api\Guru;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Guru;
use Illuminate\Http\Request;

class ProfileController extends ApiController
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

    public function profile(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (! $guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $guru = Guru::find($guruId);
        if (! $guru) {
            return $this->error('Guru tidak ditemukan', 404);
        }

        return $this->success($guru, 'Data profil guru berhasil dimuat');
    }

    public function updateProfile(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (! $guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $guru = Guru::find($guruId);
        if (! $guru) {
            return $this->error('Guru tidak ditemukan', 404);
        }

        $data = $request->validate([
            'nama_guru' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'nip' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|max:100',
        ]);

        $guru->update($data);

        return $this->success($guru, 'Profil guru berhasil diperbarui');
    }
}
