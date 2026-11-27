<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Bab;
use App\Features\Models\MataPelajaran;
use App\Features\Models\Pengaturan;
use App\Features\Models\Siswa;
use Illuminate\Http\Request;

class BabController extends ApiController
{
    public function babList(Request $request, $id)
    {
        $mapel = MataPelajaran::with(['bab.subBab'])->find($id);
        if (! $mapel) {
            $mapel = MataPelajaran::with(['bab.subBab'])->whereHas('bab')->first()
                ?? MataPelajaran::first();
        }
        if (! $mapel) {
            return $this->error('Mata pelajaran tidak ditemukan', 404);
        }

        $semesterAktif = (int) Pengaturan::ambil('semester_aktif', 1);
        $babs = Bab::where('id_mapel', $mapel->id_mapel)
            ->where('semester', $semesterAktif)
            ->orderBy('id_bab')->get();
        $isMatematika = str_contains(strtolower($mapel->nama_mapel), 'matematika');

        $babList = [];
        $unlockedCount = 0;
        foreach ($babs as $index => $b) {
            $nomor = $index + 1;
            $isLocked = (bool) ($b->is_locked ?? false);
            if (! $isLocked) {
                $unlockedCount++;
            }

            $warna = match ($nomor % 3) {
                1 => 'orange',
                2 => 'blue',
                0 => 'orange',
                default => 'blue',
            };
            if ($isLocked) {
                $warna = ($nomor % 2 == 0) ? 'blue' : 'orange';
            }

            $babList[] = [
                'id_bab' => $b->id_bab,
                'nomor' => $nomor,
                'label' => "BAB {$nomor}",
                'nama_bab' => $b->nama_bab,
                'semester' => (int) ($b->semester ?? 1),
                'is_locked' => $isLocked,
                'warna' => $warna,
                'sub_bab_count' => $b->subBab()->count(),
            ];
        }

        return $this->success([
            'mapel' => [
                'id_mapel' => $mapel->id_mapel,
                'nama' => $mapel->nama_mapel,
                'badge' => $isMatematika ? 'Matematika Seru' : 'Belajar Seru',
                'judul_seru' => $isMatematika ? 'Petualangan Angka' : 'Eksplorasi Pembelajaran',
                'progress_bab' => "{$unlockedCount}/".count($babList).' Bab',
                'tipe_icon' => $isMatematika ? 'math' : 'book',
                'semester' => $semesterAktif === 2 ? 'Semester Genap' : 'Semester Ganjil',
            ],
            'semester' => $semesterAktif === 2 ? 'Semester Genap' : 'Semester Ganjil',
            'semester_aktif' => $semesterAktif,
            'total_bab' => count($babList),
            'babs' => $babList,
        ], 'Daftar bab berhasil dimuat');
    }
}
