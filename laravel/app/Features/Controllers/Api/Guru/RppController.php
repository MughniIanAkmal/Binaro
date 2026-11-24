<?php

namespace App\Features\Controllers\Api\Guru;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Rpp;
use Illuminate\Http\Request;

class RppController extends ApiController
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

        if (!$id) {
            $firstGuru = \App\Features\Models\Guru::first();
            return $firstGuru?->id_guru;
        }

        return (int) $id;
    }

    public function listRpp(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $rpp = Rpp::where('id_guru', $guruId)->orderByDesc('created_at')->get();

        return $this->success($rpp, 'Daftar RPP berhasil dimuat');
    }

    public function createRpp(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $data = $request->validate([
            'id_rooms' => 'sometimes|integer',
            'id_mapel' => 'sometimes|integer',
            'judul_rpp' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'komponen_checklist' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        if (empty($data['id_mapel'])) {
            $data['id_mapel'] = \App\Features\Models\MataPelajaran::value('id_mapel') ?? 17;
        }

        $data['id_guru'] = $guruId;
        $rpp = Rpp::create($data);

        return $this->success($rpp, 'RPP berhasil dibuat', 201);
    }

    public function editRpp(Request $request, $id)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $rpp = Rpp::where('id_rpp', $id)->where('id_guru', $guruId)->first();
        if (!$rpp) {
            return $this->error('RPP tidak ditemukan', 404);
        }

        $data = $request->validate([
            'judul_rpp' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'komponen_checklist' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        $rpp->update($data);

        return $this->success($rpp, 'RPP berhasil diperbarui');
    }

    public function deleteRpp(Request $request, $id)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $rpp = Rpp::where('id_rpp', $id)->where('id_guru', $guruId)->first();
        if (!$rpp) {
            return $this->error('RPP tidak ditemukan', 404);
        }

        $rpp->delete();

        return $this->success(null, 'RPP berhasil dihapus');
    }
}
