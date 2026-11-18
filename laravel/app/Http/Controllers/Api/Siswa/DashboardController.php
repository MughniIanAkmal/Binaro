<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Pr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class DashboardController extends ApiController
{
    public function dashboard(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);

        $siswa = null;
        if ($siswaId) {
            $siswa = Siswa::with(['kelas', 'mataPelajaran'])->find($siswaId);
        }
        if (!$siswa) {
            $siswa = Siswa::with(['kelas', 'mataPelajaran'])->first();
        }

        $nama = $siswa ? $siswa->nm_siswa : 'Budi Santoso';
        $kelas = ($siswa && $siswa->kelas) ? $siswa->kelas->pararel : 'Kelas 5';
        $sekolah = 'SDN Kalitapen 01';

        // 1. Mata Pelajaran dengan Bab & Materi Real dari Database
        $daftarMapel = MataPelajaran::withCount('bab')->orderBy('id_mapel')->get();
        $mapelList = [];
        foreach ($daftarMapel as $i => $m) {
            $mapelName = $m->nama_mapel;
            $lower = strtolower($mapelName);

            $babCount = $m->bab_count > 0 ? $m->bab_count : 1;
            $badge = "Bab {$babCount}";

            $materiCount = Materi::whereHas('subBab.bab', fn($q) => $q->where('id_mapel', $m->id_mapel))->count();
            $materiInfo = $materiCount > 0 ? "{$materiCount} Materi Pembelajaran" : "Belum Ada Materi";
            
            // Progress dan atribut
            $progress = 0.0; $warna = ($i % 2 == 1) ? 'orange' : 'blue'; $tipe = 'shield';
            if (str_contains($lower, 'matematika')) {
                $tipe = 'math'; $warna = 'orange';
            } elseif (str_contains($lower, 'ipa') || str_contains($lower, 'alam')) {
                $tipe = 'science'; $warna = 'blue';
            } elseif (str_contains($lower, 'indonesia')) {
                $tipe = 'book'; $warna = 'blue';
            } elseif (str_contains($lower, 'pancasila')) {
                $tipe = 'shield'; $warna = 'orange';
            }

            $displayNama = match (true) {
                str_contains($lower, 'matematika') => 'Matematika',
                str_contains($lower, 'ipa') || str_contains($lower, 'alam') => 'IPA',
                str_contains($lower, 'indonesia') => 'Bhs. Indonesia',
                str_contains($lower, 'pancasila') => 'Pancasila',
                default => $mapelName,
            };

            $mapelList[] = [
                'id_mapel' => $m->id_mapel,
                'nama' => $displayNama,
                'badge' => $badge,
                'materi_info' => $materiInfo,
                'progress' => $progress,
                'persen' => 0, // Nah, refaktor, silakan isi nanti logika progress aslinya jika ingin full
                'progress_text' => '',
                'warna' => $warna,
                'tipe_icon' => $tipe,
            ];
        }

        // 2. Tugas & PR Mendatang dari Database
        $prList = Pr::with('mataPelajaran')->orderBy('tgl_tenggat')->take(5)->get();
        $tugasTotal = Pr::count();
        $tugas = [];

        foreach ($prList as $i => $pr) {
            $deadline = $pr->tgl_tenggat
                ? Carbon::parse($pr->tgl_tenggat)->translatedFormat('l, d M H.i') . ' WIB'
                : 'Tenggat belum ditentukan';

            $mapelName = strtoupper($pr->mataPelajaran->nama_mapel ?? 'UMUM');
            if (str_contains(strtolower($mapelName), 'INDONESIA')) $mapelName = 'BHS. INDONESIA';
            if (str_contains(strtolower($mapelName), 'ILMU PENGETAHUAN')) $mapelName = 'IPA';

            $tugas[] = [
                'id_pr' => $pr->id_pr,
                'mapel' => $mapelName,
                'judul' => $pr->nama_pr,
                'deadline' => "Tenggat: {$deadline}",
                'warna' => ($i % 2 == 1) ? 'orange' : 'blue',
                'tipe_icon' => match(true) {
                    str_contains(strtolower($mapelName), 'ipa') => 'science',
                    str_contains(strtolower($mapelName), 'indonesia') => 'book',
                    default => 'math',
                },
            ];
        }

        return $this->success([
            'siswa' => [
                'id_siswa' => $siswa?->id_siswa ?? 1,
                'nama' => $nama,
                'nm_siswa' => $nama,
                'kelas' => $kelas . ' • ' . $sekolah,
                'foto_profil' => $siswa?->foto_profil,
            ],
            'menu' => [
                ['title' => 'Mapel', 'icon' => 'menu_book', 'color' => 'blue'],
                ['title' => 'Ujian', 'icon' => 'quiz', 'color' => 'orange'],
                ['title' => 'Jadwal', 'icon' => 'calendar', 'color' => 'blue'],
            ],
            'tugas' => $tugas,
            'tugas_total' => $tugasTotal,
            'mata_pelajaran' => $mapelList,
        ], 'Dashboard berhasil dimuat');
    }

    // Bisa pindahkan ke trait/helper jika lebih dari 1 controller butuh
    protected function resolveSiswaId(Request $request): ?int
    {
        $user = $request->user();
        if ($user && isset($user->id_siswa)) {
            return (int) $user->id_siswa;
        }
        $id = $request->input('id_siswa') ?? $request->query('id_siswa') ?? $request->query('id') ?? Session::get('siswa_id') ?? Session::get('user_id');
        if ($id) return (int) $id;
        $first = Siswa::first();
        return $first?->id_siswa;
    }
}
