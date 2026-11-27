<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\MataPelajaran;
use App\Features\Models\Pengaturan;
use App\Features\Models\Siswa;
use Illuminate\Http\Request;

class MapelController extends ApiController
{
    public function mapelList(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        if (! $siswaId) {
            return $this->error('User tidak terautentikasi', 401);
        }
        $siswa = Siswa::find($siswaId);
        $namaKelas = $siswa?->nama_kelas ?? '-';
        $semesterAktif = (int) Pengaturan::ambil('semester_aktif', 1);
        $daftarMapel = MataPelajaran::withCount(['bab' => function ($q) use ($semesterAktif) {
            $q->where('semester', $semesterAktif);
        }])->orderBy('id_mapel')->get();

        $result = [];
        foreach ($daftarMapel as $m) {
            $name = $m->nama_mapel;
            $lower = strtolower($name);

            $icon = 'book';
            $warna = 'blue';
            $jadwal = 'Senin & Rabu';
            $wajib = true;
            $babTotal = $m->bab_count;
            $babRentang = $babTotal > 0 ? "Bab 1 - {$babTotal}" : 'Belum Ada Bab';

            $progData = app(DashboardController::class)->calculateMapelProgress($m->id_mapel, $siswaId);
            $progress = $progData['progress'];

            if (str_contains($lower, 'matematika')) {
                $icon = 'math';
                $jadwal = 'Senin & Rabu';
                $wajib = true;
            } elseif (str_contains($lower, 'ipa') || str_contains($lower, 'alam')) {
                $icon = 'science';
                $jadwal = 'Selasa & Kamis';
                $wajib = true;
            } elseif (str_contains($lower, 'indonesia')) {
                $icon = 'book';
                $jadwal = 'Senin & Jumat';
                $wajib = true;
            } elseif (str_contains($lower, 'pancasila') || str_contains($lower, 'kewarganegaraan')) {
                $icon = 'star';
                $jadwal = 'Rabu';
                $wajib = true;
            } elseif (str_contains($lower, 'seni') || str_contains($lower, 'sbdp')) {
                $icon = 'palette';
                $jadwal = 'Sabtu';
                $wajib = false;
            } elseif (str_contains($lower, 'inggris')) {
                $icon = 'chat';
                $jadwal = 'Kamis';
                $wajib = false;
            }

            $result[] = [
                'id_mapel' => $m->id_mapel,
                'nama' => $name,
                'deskripsi' => $m->deskripsi,
                'bab_total' => $babTotal,
                'bab_rentang' => $babRentang,
                'progress' => $progress,
                'persen' => $progData['persen'],
                'progress_text' => $progData['progress_text'],
                'bab_unlocked' => $progData['bab_unlocked'],
                'bab_selesai' => $progData['bab_selesai'],
                'jadwal' => $jadwal,
                'warna' => $warna,
                'tipe_icon' => $icon,
                'is_wajib' => $wajib,
            ];
        }

        return $this->success([
            'kelas' => 'Mata Pelajaran '.$namaKelas,
            'semester' => $semesterAktif === 2 ? 'Semester Genap' : 'Semester Ganjil',
            'semester_aktif' => $semesterAktif,
            'total_mapel' => count($result),
            'wajib_count' => count(array_filter($result, fn ($x) => $x['is_wajib'])),
            'mapel' => $result,
        ], 'Daftar mata pelajaran berhasil dimuat');
    }
}
