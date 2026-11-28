<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Bab;
use App\Features\Models\HasilKuisSiswa;
use App\Features\Models\MataPelajaran;
use App\Features\Models\Materi;
use App\Features\Models\Pr;
use App\Features\Models\Quiz;
use App\Features\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends ApiController
{
    public function dashboard(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        if (! $siswaId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $siswa = Siswa::with(['kelas', 'mataPelajaran'])->find($siswaId);
        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $nama = $siswa ? $siswa->nm_siswa : 'Budi Santoso';
        $kelas = ($siswa && $siswa->kelas) ? $siswa->kelas->pararel : 'Kelas 5';
        $sekolah = 'SDN Kalitapen 01';

        // 1. Mata Pelajaran dengan Bab & Materi Real dari Database
        // Mapel difilter per kelas siswa (1 mapel = 1 kelas pengampu).
        $daftarMapel = collect();
        if ($siswa->id_rooms) {
            $daftarMapel = MataPelajaran::withCount('bab')
                ->where('id_rooms', $siswa->id_rooms)
                ->orderBy('id_mapel')->get();
        }
        $mapelList = [];
        foreach ($daftarMapel as $i => $m) {
            $mapelName = $m->nama_mapel;
            $lower = strtolower($mapelName);

            $babCount = $m->bab_count > 0 ? $m->bab_count : 1;
            $badge = "Bab {$babCount}";

            $materiCount = Materi::whereHas('subBab.bab', fn ($q) => $q->where('id_mapel', $m->id_mapel))->count();
            $materiInfo = $materiCount > 0 ? "{$materiCount} Materi Pembelajaran" : 'Belum Ada Materi';

            // Progress dan atribut
            $progress = 0.0;
            $warna = ($i % 2 == 1) ? 'orange' : 'blue';
            $tipe = 'shield';
            if (str_contains($lower, 'matematika')) {
                $tipe = 'math';
                $warna = 'orange';
            } elseif (str_contains($lower, 'ipa') || str_contains($lower, 'alam')) {
                $tipe = 'science';
                $warna = 'blue';
            } elseif (str_contains($lower, 'indonesia')) {
                $tipe = 'book';
                $warna = 'blue';
            } elseif (str_contains($lower, 'pancasila')) {
                $tipe = 'shield';
                $warna = 'orange';
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
                ? Carbon::parse($pr->tgl_tenggat)->translatedFormat('l, d M H.i').' WIB'
                : 'Tenggat belum ditentukan';

            $mapelName = strtoupper($pr->mataPelajaran->nama_mapel ?? 'UMUM');
            if (str_contains(strtolower($mapelName), 'INDONESIA')) {
                $mapelName = 'BHS. INDONESIA';
            }
            if (str_contains(strtolower($mapelName), 'ILMU PENGETAHUAN')) {
                $mapelName = 'IPA';
            }

            $tugas[] = [
                'id_pr' => $pr->id_pr,
                'mapel' => $mapelName,
                'judul' => $pr->nama_pr,
                'deadline' => "Tenggat: {$deadline}",
                'warna' => ($i % 2 == 1) ? 'orange' : 'blue',
                'tipe_icon' => match (true) {
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
                'kelas' => $kelas.' • '.$sekolah,
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

    /**
     * Hitung progress kuis siswa per mata pelajaran berdasarkan bab yang terbuka (unlocked).
     * Public karena dipanggil lintas controller dari Api\Siswa\MapelController.
     */
    public function calculateMapelProgress(int $idMapel, int $idSiswa): array
    {
        $unlockedBabsQuery = Bab::where('id_mapel', $idMapel);
        if (Schema::hasColumn('bab', 'is_locked')) {
            $unlockedBabsQuery->where('is_locked', false);
        }
        $unlockedBabs = $unlockedBabsQuery->get();

        $totalUnlocked = $unlockedBabs->count();
        if ($totalUnlocked === 0) {
            return ['progress' => 0.0, 'persen' => 0, 'bab_selesai' => 0, 'bab_unlocked' => 0, 'progress_text' => '0/0 Bab (0%)'];
        }

        $completedBabCount = 0;
        foreach ($unlockedBabs as $bab) {
            $subBabIds = $bab->subBab()->pluck('id_sub_bab');
            if ($subBabIds->isEmpty()) {
                continue;
            }

            $materiIds = Materi::whereIn('id_sub_bab', $subBabIds)->pluck('id_materi');
            $quizIdsFromSubBab = Quiz::whereIn('id_sub_bab', $subBabIds)->pluck('id_quiz');
            $quizIdsFromMateriField = Quiz::whereIn('id_materi', $materiIds)->pluck('id_quiz');
            $allQuizIds = $quizIdsFromSubBab->merge($quizIdsFromMateriField)->unique()->filter()->values();

            if ($allQuizIds->isNotEmpty()) {
                $hasCompleted = HasilKuisSiswa::whereIn('id_quiz', $allQuizIds)
                    ->where('id_siswa', $idSiswa)->exists();
                if ($hasCompleted) {
                    $completedBabCount++;
                }
            }
        }

        $progressRatio = min(1.0, max(0.0, round($completedBabCount / $totalUnlocked, 2)));
        $persen = (int) round($progressRatio * 100);

        return [
            'progress' => $progressRatio,
            'persen' => $persen,
            'bab_selesai' => $completedBabCount,
            'bab_unlocked' => $totalUnlocked,
            'progress_text' => "{$completedBabCount}/{$totalUnlocked} Bab ({$persen}%)",
        ];
    }
}
