<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Bab;
use App\Features\Models\Siswa;
use App\Features\Models\SubBab;
use Illuminate\Http\Request;

class SubBabController extends ApiController
{
    public function subBabList(Request $request, $id)
    {
        $bab = Bab::with('mataPelajaran')->find($id);
        if (! $bab) {
            $bab = Bab::with('mataPelajaran')->whereHas('subBab')->first()
                ?? Bab::with('mataPelajaran')->first();
        }
        if (! $bab) {
            return $this->error('Bab tidak ditemukan', 404);
        }

        $subBabs = SubBab::where('id_bab', $bab->id_bab)->orderBy('id_sub_bab')->get();

        $subList = [];
        foreach ($subBabs as $idx => $sb) {
            $nomor = $idx + 1;
            $isLocked = (bool) ($sb->is_locked ?? false);
            $isActive = ! $isLocked;

            $tipeIcon = match ($nomor) {
                1 => 'cake',
                2 => 'scale',
                3 => 'fraction',
                default => 'lock',
            };

            $subList[] = [
                'id_sub_bab' => $sb->id_sub_bab,
                'nomor' => $nomor,
                'label' => "Sub Bab {$nomor}".($isActive ? ' •' : ''),
                'nama_sub_bab' => $sb->nama_sub_bab,
                'deskripsi' => $isActive ? 'Besar, kecil, dan sama rasa!' : null,
                'tipe_icon' => $tipeIcon,
                'is_locked' => $isLocked,
                'is_active' => $isActive,
                'materi_count' => $sb->materi()->count(),
            ];
        }

        return $this->success([
            'bab' => [
                'id_bab' => $bab->id_bab,
                'nomor' => 1,
                'nama_mapel' => strtoupper($bab->mataPelajaran->nama_mapel ?? 'MATEMATIKA'),
                'nama_bab' => $bab->nama_bab,
                'total_sub_bab' => count($subList),
                'info_sub' => count($subList).' Sub Bab Pembelajaran',
            ],
            'sub_babs' => $subList,
        ], 'Daftar sub bab berhasil dimuat');
    }

    protected function resolveSiswaId(Request $request): ?int
    {
        $id = $request->input('id_siswa') ?? $request->query('id_siswa') ?? $request->query('id');
        if ($id) {
            return (int) $id;
        }

        return Siswa::first()?->id_siswa;
    }
}
