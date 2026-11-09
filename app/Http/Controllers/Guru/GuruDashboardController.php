<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Guru;
use App\Models\HasilKuisSiswa;
use App\Models\JadwalMataPelajaran;
use App\Models\Quiz;
use App\Models\Rpp;
use App\Models\Siswa;
use Carbon\Carbon;

class GuruDashboardController extends Controller
{
    public function index()
    {
        if (session('user_type') !== 'guru') {
            return redirect()->route('login');
        }

        $guru = Guru::find(session('user_id'));
        if (! $guru) {
            session()->flush();

            return redirect()->route('login')->with('error', 'Sesi guru tidak valid. Silakan login kembali.');
        }

        $now = Carbon::now();
        $hariIni = $this->namaHari($now);
        $menitSekarang = ($now->hour * 60) + $now->minute;

        $jadwalGuru = JadwalMataPelajaran::with(['mataPelajaran', 'kelas'])
            ->where('id_guru', $guru->id_guru)
            ->get();

        // ---- Sesi hari ini -------------------------------------------------
        $sesiHariIni = $jadwalGuru
            ->filter(fn ($j) => strcasecmp(trim((string) $j->hari), $hariIni) === 0)
            ->sortBy(fn ($j) => $this->parseJam($j->jam)['mulai'] ?? 9999)
            ->values()
            ->map(function ($j) use ($menitSekarang) {
                $range = $this->parseJam($j->jam);
                $status = 'terjadwal';
                if ($range['selesai'] !== null && $menitSekarang >= $range['selesai']) {
                    $status = 'selesai';
                } elseif ($range['mulai'] !== null && $range['selesai'] !== null
                    && $menitSekarang >= $range['mulai'] && $menitSekarang < $range['selesai']) {
                    $status = 'berlangsung';
                }

                $idRooms = $j->id_rooms;
                $kelasPendek = $this->kelasPendek($j->kelas->pararel ?? null);

                return [
                    'id_jadwal' => $j->id_jadwal,
                    'id_mapel' => $j->id_mapel,
                    'id_rooms' => $idRooms,
                    'mapel' => $j->mataPelajaran->nama_mapel ?? 'Mata Pelajaran',
                    'kelas' => $kelasPendek,
                    'jam' => $j->jam ?? '-',
                    'mulai_label' => $range['mulai_label'],
                    'selesai_label' => $range['selesai_label'],
                    'durasi_menit' => $range['durasi'],
                    'jp' => $range['durasi'] ? max(1, (int) round($range['durasi'] / 45)) : 2,
                    'jumlah_siswa' => $idRooms ? Siswa::where('id_rooms', $idRooms)->count() : 0,
                    'status' => $status,
                ];
            });

        $totalJp = $sesiHariIni->sum('jp');
        $sesiBerikutnya = $sesiHariIni->firstWhere('status', 'berlangsung')
            ?? $sesiHariIni->firstWhere('status', 'terjadwal');

        // Sesi berikutnya di hari lain, dipakai saat sesi hari ini sudah tuntas.
        $sesiMendatang = null;
        if (! $sesiBerikutnya && $jadwalGuru->isNotEmpty()) {
            $urutanHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $indexHariIni = array_search($hariIni, $urutanHari, true);

            for ($langkah = 1; $langkah <= 7 && ! $sesiMendatang; $langkah++) {
                $hariTarget = $urutanHari[($indexHariIni + $langkah) % 7];
                $kandidat = $jadwalGuru
                    ->filter(fn ($j) => strcasecmp(trim((string) $j->hari), $hariTarget) === 0)
                    ->sortBy(fn ($j) => $this->parseJam($j->jam)['mulai'] ?? 9999)
                    ->first();

                if ($kandidat) {
                    $range = $this->parseJam($kandidat->jam);
                    $tanggalTarget = $now->copy()->addDays($langkah);
                    $sesiMendatang = [
                        'hari' => $hariTarget,
                        'tanggal' => $tanggalTarget->locale('id')->translatedFormat('d M'),
                        'mapel' => $kandidat->mataPelajaran->nama_mapel ?? 'Mata Pelajaran',
                        'kelas' => $this->kelasPendek($kandidat->kelas->pararel ?? null),
                        'mulai_label' => $range['mulai_label'],
                        'selesai_label' => $range['selesai_label'],
                    ];
                }
            }
        }

        // ---- Siswa yang diajar ---------------------------------------------
        $rombelIds = $jadwalGuru->pluck('id_rooms')->filter()->unique()->values();
        $siswaIds = $rombelIds->isEmpty()
            ? collect()
            : Siswa::whereIn('id_rooms', $rombelIds)->pluck('id_siswa');

        $totalSiswa = $siswaIds->count();
        $siswaAktif = $siswaIds->isEmpty() ? 0 : Absen::whereIn('id_siswa', $siswaIds)
            ->where('tanggal', '>=', $now->copy()->subDays(7)->toDateString())
            ->where('status', 'Hadir')
            ->distinct()
            ->count('id_siswa');
        $persenAktif = $totalSiswa > 0 ? (int) round($siswaAktif / $totalSiswa * 100) : 0;

        // ---- Tugas / kuis yang perlu dikoreksi ------------------------------
        $quizIds = Quiz::where('id_guru', $guru->id_guru)->pluck('id_quiz');

        $perluDinilai = $quizIds->isEmpty() ? 0 : HasilKuisSiswa::whereIn('id_quiz', $quizIds)
            ->where('status_kirim', false)
            ->count();

        $tugasPerluDinilai = $quizIds->isEmpty()
            ? collect()
            : Quiz::with('mataPelajaran')
                ->whereIn('id_quiz', $quizIds)
                ->withCount([
                    'hasilSiswa as total_terkumpul',
                    'hasilSiswa as belum_dinilai' => fn ($q) => $q->where('status_kirim', false),
                ])
                ->get()
                ->filter(fn ($q) => $q->belum_dinilai > 0)
                ->sortByDesc('belum_dinilai')
                ->take(5)
                ->values()
                ->map(function ($q) use ($quizIds) {
                    $terakhir = HasilKuisSiswa::where('id_quiz', $q->id_quiz)->max('updated_at');

                    return [
                        'id_quiz' => $q->id_quiz,
                        'judul' => $q->judul_quiz ?: ($q->nama_quiz ?: 'Kuis'),
                        'mapel' => $q->mataPelajaran->nama_mapel ?? 'Mata Pelajaran',
                        'terkumpul' => $q->total_terkumpul,
                        'belum_dinilai' => $q->belum_dinilai,
                        'tanggal' => $terakhir ? Carbon::parse($terakhir) : null,
                    ];
                });

        // ---- Kelengkapan RPP ------------------------------------------------
        $mapelIds = $jadwalGuru->pluck('id_mapel')->filter()->unique()->values();
        $rppTerverifikasi = Rpp::where('id_guru', $guru->id_guru)
            ->where('status', 'terverifikasi')
            ->when($mapelIds->isNotEmpty(), fn ($q) => $q->whereIn('id_mapel', $mapelIds))
            ->count();
        $rppTotal = $mapelIds->isNotEmpty()
            ? $mapelIds->count()
            : Rpp::where('id_guru', $guru->id_guru)->count();
        $persenRpp = $rppTotal > 0 ? (int) round($rppTerverifikasi / $rppTotal * 100) : 0;

        // ---- Semester aktif -------------------------------------------------
        $bulan = (int) $now->month;
        $semester = $bulan >= 7 ? 'GANJIL' : 'GENAP';
        $tahunAjaran = $bulan >= 7
            ? $now->year.'/'.($now->year + 1)
            : ($now->year - 1).'/'.$now->year;

        $rombelLabel = $rombelIds->isEmpty()
            ? 'Belum ada rombel'
            : $jadwalGuru->pluck('kelas.pararel')->filter()->unique()
                ->map(fn ($p) => $this->kelasPendek($p))->implode(', ');

        $mapelDiampu = $jadwalGuru->pluck('mataPelajaran.nama_mapel')->filter()->unique()->values();

        return view('guru.dashboard', compact(
            'guru',
            'hariIni',
            'sesiHariIni',
            'sesiBerikutnya',
            'sesiMendatang',
            'totalJp',
            'totalSiswa',
            'siswaAktif',
            'persenAktif',
            'perluDinilai',
            'tugasPerluDinilai',
            'rppTerverifikasi',
            'rppTotal',
            'persenRpp',
            'semester',
            'tahunAjaran',
            'rombelIds',
            'rombelLabel',
            'mapelDiampu',
        ));
    }

    /**
     * "Kelas 1B" -> "1B" supaya tidak jadi "Kelas Kelas 1B" di tampilan.
     */
    private function kelasPendek(?string $pararel): string
    {
        $pararel = trim((string) $pararel);
        if ($pararel === '') {
            return '-';
        }

        return preg_replace('/^kelas\s+/i', '', $pararel) ?: $pararel;
    }

    private function namaHari(Carbon $tanggal): string
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][$tanggal->dayOfWeekIso] ?? 'Senin';
    }

    /**
     * Normalisasi format jam jadwal ("07.00-10.00", "10:15 - 11:45", "07.00 s/d 08.30").
     *
     * @return array{mulai: int|null, selesai: int|null, durasi: int|null, mulai_label: string, selesai_label: string}
     */
    private function parseJam(?string $jam): array
    {
        $kosong = ['mulai' => null, 'selesai' => null, 'durasi' => null, 'mulai_label' => '-', 'selesai_label' => '-'];

        if (! $jam) {
            return $kosong;
        }

        if (! preg_match_all('/(\d{1,2})\s*[.:]\s*(\d{2})/', $jam, $cocok, PREG_SET_ORDER) || count($cocok) < 2) {
            return $kosong;
        }

        $keMenit = fn ($jam_, $menit_) => ((int) $jam_ * 60) + (int) $menit_;
        $label = fn ($jam_, $menit_) => sprintf('%02d:%02d', (int) $jam_, (int) $menit_);

        $mulai = $keMenit($cocok[0][1], $cocok[0][2]);
        $selesai = $keMenit($cocok[1][1], $cocok[1][2]);

        return [
            'mulai' => $mulai,
            'selesai' => $selesai,
            'durasi' => max(0, $selesai - $mulai),
            'mulai_label' => $label($cocok[0][1], $cocok[0][2]),
            'selesai_label' => $label($cocok[1][1], $cocok[1][2]),
        ];
    }
}
