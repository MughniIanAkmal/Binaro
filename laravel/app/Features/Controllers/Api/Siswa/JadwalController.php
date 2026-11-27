<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Guru;
use App\Features\Models\Jadwal;
use App\Features\Models\MataPelajaran;
use App\Features\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalController extends ApiController
{
    public function jadwalList(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        if (! $siswaId) {
            return $this->error('User tidak terautentikasi', 401);
        }
        $siswa = Siswa::find($siswaId);

        $rows = collect();
        if ($siswa && $siswa->id_rooms) {
            $rows = Jadwal::where('id_rooms', $siswa->id_rooms)->get();
        }

        $mapelNames = MataPelajaran::pluck('nama_mapel', 'id_mapel');
        $guruNames = Guru::pluck('nama_guru', 'id_guru');
        $urutanHari = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

        $result = $rows->map(function ($j) use ($mapelNames, $guruNames) {
            $name = $mapelNames[$j->id_mapel] ?? 'Mata Pelajaran';
            $lower = strtolower($name);
            $icon = 'book';
            if (str_contains($lower, 'matematika')) {
                $icon = 'math';
            } elseif (str_contains($lower, 'ipa') || str_contains($lower, 'alam')) {
                $icon = 'science';
            } elseif (str_contains($lower, 'pancasila') || str_contains($lower, 'kewarganegaraan')) {
                $icon = 'star';
            } elseif (str_contains($lower, 'seni') || str_contains($lower, 'sbdp')) {
                $icon = 'palette';
            } elseif (str_contains($lower, 'inggris')) {
                $icon = 'chat';
            }

            return [
                'id_jadwal' => $j->id_jadwal,
                'hari' => $j->hari,
                'jam' => $j->jam,
                'nama_mapel' => $name,
                'tipe_icon' => $icon,
                'guru' => $guruNames[$j->id_guru] ?? null,
            ];
        })->sortBy([
            fn ($a, $b) => ($urutanHari[$a['hari']] ?? 9) <=> ($urutanHari[$b['hari']] ?? 9),
            fn ($a, $b) => strcmp((string) $a['jam'], (string) $b['jam']),
        ])->values();

        $namaKelas = null;
        if ($siswa && $siswa->id_rooms) {
            $namaKelas = DB::table('kelas')->where('id_rooms', $siswa->id_rooms)->value('pararel');
        }

        return $this->success([
            'kelas' => $namaKelas,
            'jadwal' => $result,
        ], 'Jadwal pelajaran berhasil dimuat');
    }
}
