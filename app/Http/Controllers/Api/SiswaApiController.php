<?php

namespace App\Http\Controllers\Api;

use App\Models\Absen;
use App\Models\Bab;
use App\Models\HasilKuisSiswa;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Pr;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use App\Models\SubBab;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SiswaApiController extends ApiController
{
    public function __construct(private AbsensiService $absensiService) {}

    // GET /api/siswa/dashboard
    public function dashboard(Request $request)
    {
        $siswaId = $request->query('id_siswa') ?? $request->query('id') ?? Session::get('user_id');

        $siswa = null;
        if ($siswaId) {
            $siswa = Siswa::with(['kelas', 'mataPelajaran'])->find($siswaId);
        }
        if (!$siswa) {
            $siswa = Siswa::with(['kelas', 'mataPelajaran'])->where('nm_siswa', 'like', '%Budi%')->first()
                ?? Siswa::with(['kelas', 'mataPelajaran'])->first();
        }

        $nama = $siswa ? $siswa->nm_siswa : 'Budi Santoso';
        $kelas = ($siswa && $siswa->kelas) ? $siswa->kelas->pararel : 'Kelas 5';
        $sekolah = 'SDN Kalitapen 01';

        // 1. Mata Pelajaran dengan Bab & Materi
        $daftarMapel = MataPelajaran::withCount('bab')->orderBy('id_mapel')->get();
        $mapelList = [];
        foreach ($daftarMapel as $i => $m) {
            $mapelName = $m->nama_mapel;
            $lower = strtolower($mapelName);

            $badge = 'Bab ' . ($m->bab_count > 0 ? $m->bab_count : 2);
            $materiTotal = 16;
            $materiSelesai = 10;
            $progress = 0.62;
            $warna = 'blue';
            $tipe = 'shield';

            if (str_contains($lower, 'matematika')) {
                $tipe = 'math';
                $warna = 'orange';
                $badge = 'Bab 3';
                $materiTotal = 20;
                $materiSelesai = 14;
                $progress = 0.70;
            } elseif (str_contains($lower, 'ipa') || str_contains($lower, 'alam')) {
                $tipe = 'science';
                $warna = 'blue';
                $badge = 'Bab 4';
                $materiTotal = 16;
                $materiSelesai = 10;
                $progress = 0.62;
            } elseif (str_contains($lower, 'indonesia')) {
                $tipe = 'book';
                $warna = 'blue';
                $badge = 'Bab 2';
                $materiTotal = 15;
                $materiSelesai = 12;
                $progress = 0.80;
            } elseif (str_contains($lower, 'pancasila')) {
                $tipe = 'shield';
                $warna = 'orange';
                $badge = 'Bab 2';
                $materiTotal = 12;
                $materiSelesai = 8;
                $progress = 0.66;
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
                'materi_info' => "{$materiSelesai} dari {$materiTotal} Materi",
                'progress' => $progress,
                'warna' => $warna,
                'tipe_icon' => $tipe,
            ];
        }

        // 2. Tugas & PR Mendatang dari Database
        $prList = Pr::with('mataPelajaran')->orderBy('tgl_tenggat')->take(5)->get();
        $tugasTotal = Pr::count();
        $tugas = [];

        foreach ($prList as $i => $pr) {
            $deadline = 'Besok, 07.15 WIB';
            if ($i == 1) $deadline = 'Senin, 07.30 WIB';
            if ($i == 2) $deadline = 'Jumat, 10.00 WIB';

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
                'id_siswa' => $siswa?->id_siswa ?? 7,
                'nama' => $nama,
                'kelas' => $kelas . ' • ' . $sekolah,
                'foto_profil' => $siswa?->foto_profil,
            ],
            'menu' => [
                ['title' => 'Mapel', 'icon' => 'menu_book', 'color' => 'blue'],
                ['title' => 'Ujian', 'icon' => 'quiz', 'color' => 'orange'],
                ['title' => 'Jadwal', 'icon' => 'calendar', 'color' => 'blue'],
            ],
            'tugas' => $tugas,
            'tugas_total' => $tugasTotal > 0 ? $tugasTotal : 5,
            'mata_pelajaran' => $mapelList,
        ]);
    }

    // GET /api/siswa/profile
    public function profile(Request $request)
    {
        $siswaId = $request->query('id') ?? $request->query('id_siswa');

        if ($siswaId) {
            $siswa = Siswa::with(['barcode', 'kelas', 'absens' => function ($query) {
                $query->orderByDesc('tanggal')->orderByDesc('waktu_absen');
            }])->find($siswaId);
        } else {
            $userId = Session::get('user_id');
            if ($userId) {
                $siswa = Siswa::with(['barcode', 'kelas', 'absens' => function ($query) {
                    $query->orderByDesc('tanggal')->orderByDesc('waktu_absen');
                }])->find($userId);
            } else {
                $siswa = Siswa::with(['barcode', 'kelas'])->first();
            }
        }

        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $barcodeCode = $siswa->barcode?->kode_barcode ?? ('SISWA-' . $siswa->nisn);

        // Hitung absensi bulan ini
        $hadir = $siswa->absens->where('status', 'Hadir')->count();
        $izin = $siswa->absens->where('status', 'Izin')->count();
        $sakit = $siswa->absens->where('status', 'Sakit')->count();
        $alpa = $siswa->absens->where('status', 'Alpa')->count();

        return $this->success([
            'siswa' => [
                'id_siswa' => $siswa->id_siswa,
                'nama' => $siswa->nm_siswa,
                'kelas' => $siswa->nama_kelas . ' • SDN Kalitapen 01',
                'nisn' => $siswa->nisn,
                'email' => $siswa->email,
                'no_hp' => $siswa->no_hp,
                'foto_profil' => $siswa->foto_profil,
                'barcode' => $barcodeCode,
            ],
            'rekap_absensi' => [
                'bulan' => Carbon::now()->translatedFormat('F Y'),
                'hadir' => $hadir > 0 ? $hadir : 18,
                'izin' => $izin > 0 ? $izin : 1,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'hari_ini' => [
                    'status' => 'Sudah Hadir',
                    'keterangan' => 'Tepat Waktu • 07.10 WIB',
                    'badge' => '95% Rajin',
                ]
            ],
            'data_sekolah' => [
                'wali' => 'Ibu Endang Rahayu',
                'no_absen' => str_pad($siswa->id_siswa, 2, '0', STR_PAD_LEFT),
                'tahun_ajaran' => '2024/2025 (Genap)',
            ],
            'versi' => 'Sahabat Belajar v1.2.0-SD',
        ]);
    }

    // POST /api/siswa/update-password
    public function updatePassword(Request $request)
    {
        $siswaId = $request->input('id_siswa') ?? $request->query('id_siswa') ?? Session::get('user_id');
        $siswa = Siswa::find($siswaId);
        if (!$siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:4',
            'konfirmasi_password' => 'required|string|same:password_baru',
        ]);

        // Verifikasi kecocokan password lama
        $stored = $siswa->password;
        $input = trim($request->input('password_lama'));
        $match = ($input === $stored) || (md5($input) === $stored) || (Hash::check($input, $stored));

        if (!$match) {
            return $this->error('Sandi saat ini (password lama) salah!', 400);
        }

        $siswa->password = Hash::make($request->input('password_baru'));
        $siswa->save();

        return $this->success(null, 'Kata sandi berhasil diperbarui!');
    }

    // POST /api/siswa/profile
    public function updateProfile(Request $request)
    {
        $userId = Session::get('user_id');
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $siswa = Siswa::find($userId);
        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $data = $request->validate([
            'nm_siswa' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'foto_profil' => 'sometimes|url',
        ]);

        $siswa->update($data);

        return $this->success($siswa, 'Profil berhasil diperbarui');
    }

    // GET /api/siswa/absensi-hari-ini
    public function todayAttendance(Request $request)
    {
        $userId = Session::get('user_id');
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $today = now()->toDateString();
        $absen = Absen::where('id_siswa', $userId)
            ->where('tanggal', $today)
            ->first();

        return $this->success($absen);
    }

    // POST /api/siswa/absensi-scan
    public function selfScan(Request $request)
    {
        $data = $request->validate([
            'id_barcode' => 'required|exists:barcode,id_barcode',
        ]);

        $userId = Session::get('user_id');
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $barcode = DB::table('barcode')
            ->where('id_barcode', $data['id_barcode'])
            ->first();

        if (! $barcode || $barcode->id_siswa != $userId) {
            return $this->error('QR tidak valid', 404);
        }

        $hasil = $this->absensiService->catatScan($barcode->kode_barcode, null, 'scan_qr');

        if ($hasil['hasil'] === 'duplikat') {
            return $this->error($hasil['message'], 409);
        }

        $siswa = Siswa::find($userId);

        return $this->success([
            'siswa' => $siswa->nm_siswa,
            'nisn' => $siswa->nisn,
            'status' => 'Hadir',
            'keterangan' => $hasil['keterangan'],
            'waktu_absen' => $hasil['absen']->waktu_absen,
            'metode' => 'scan_qr',
        ], $hasil['upgrade'] ? 'Absensi diperbarui dari status Alpa' : 'Absensi registrasi', 201);
    }

    // =========================================================================
    // FITUR MAPEL, BAB, SUB-BAB, DAN MATERI PEMBELAJARAN
    // =========================================================================

    // GET /api/siswa/mapel
    public function mapelList(Request $request)
    {
        $daftarMapel = MataPelajaran::withCount('bab')->orderBy('id_mapel')->get();

        $result = [];
        foreach ($daftarMapel as $m) {
            $name = $m->nama_mapel;
            $lower = strtolower($name);

            $icon = 'book';
            $warna = 'blue';
            $progress = 0.50;
            $jadwal = 'Senin & Rabu';
            $wajib = true;
            $babTotal = $m->bab_count > 0 ? $m->bab_count : 6;

            if (str_contains($lower, 'matematika')) {
                $icon = 'math';
                $progress = 0.72;
                $jadwal = 'Senin & Rabu';
                $wajib = true;
                $babTotal = 6;
            } elseif (str_contains($lower, 'ipa') || str_contains($lower, 'alam')) {
                $icon = 'science';
                $progress = 0.58;
                $jadwal = 'Selasa & Kamis';
                $wajib = true;
                $babTotal = 6;
            } elseif (str_contains($lower, 'indonesia')) {
                $icon = 'book';
                $progress = 0.84;
                $jadwal = 'Senin & Jumat';
                $wajib = true;
                $babTotal = 7;
            } elseif (str_contains($lower, 'pancasila') || str_contains($lower, 'kewarganegaraan')) {
                $icon = 'star';
                $progress = 0.65;
                $jadwal = 'Rabu';
                $wajib = true;
                $babTotal = 5;
            } elseif (str_contains($lower, 'seni') || str_contains($lower, 'sbdp')) {
                $icon = 'palette';
                $progress = 0.40;
                $jadwal = 'Sabtu';
                $wajib = false;
                $babTotal = 4;
            } elseif (str_contains($lower, 'inggris')) {
                $icon = 'chat';
                $progress = 0.50;
                $jadwal = 'Kamis';
                $wajib = false;
                $babTotal = 6;
            }

            $result[] = [
                'id_mapel' => $m->id_mapel,
                'nama' => $name,
                'deskripsi' => $m->deskripsi,
                'bab_total' => $babTotal,
                'bab_rentang' => "Bab 1 - {$babTotal}",
                'progress' => $progress,
                'jadwal' => $jadwal,
                'warna' => $warna,
                'tipe_icon' => $icon,
                'is_wajib' => $wajib,
            ];
        }

        return $this->success([
            'kelas' => 'Mata Pelajaran Kelas 5 SD • Semester 2',
            'total_mapel' => count($result),
            'wajib_count' => count(array_filter($result, fn($x) => $x['is_wajib'])),
            'mapel' => $result,
        ]);
    }

    // GET /api/siswa/mapel/{id}/bab
    public function babList(Request $request, $id)
    {
        $mapel = MataPelajaran::with(['bab.subBab'])->find($id);
        if (!$mapel) {
            return $this->error('Mata pelajaran tidak ditemukan', 404);
        }

        $babs = Bab::where('id_mapel', $id)->orderBy('id_bab')->get();
        $isMatematika = str_contains(strtolower($mapel->nama_mapel), 'matematika');

        $babList = [];
        $unlockedCount = 0;
        foreach ($babs as $index => $b) {
            $nomor = $index + 1;
            $isLocked = ($nomor > 3); // 3 bab pertama terbuka
            if (!$isLocked) $unlockedCount++;

            // Warna selang-seling sesuai desain screenshot
            $warna = match ($nomor % 3) {
                1 => 'orange',
                2 => 'blue',
                0 => 'orange',
                default => 'blue',
            };
            if ($isLocked) $warna = ($nomor % 2 == 0) ? 'blue' : 'orange';

            $babList[] = [
                'id_bab' => $b->id_bab,
                'nomor' => $nomor,
                'label' => "BAB {$nomor}",
                'nama_bab' => $b->nama_bab,
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
                'progress_bab' => "{$unlockedCount}/" . count($babList) . " Bab",
                'tipe_icon' => $isMatematika ? 'math' : 'book',
            ],
            'total_bab' => count($babList),
            'babs' => $babList,
        ]);
    }

    // GET /api/siswa/bab/{id}/sub-bab
    public function subBabList(Request $request, $id)
    {
        $bab = Bab::with('mataPelajaran')->find($id);
        if (!$bab) {
            return $this->error('Bab tidak ditemukan', 404);
        }

        $subBabs = SubBab::where('id_bab', $id)->orderBy('id_sub_bab')->get();

        $subList = [];
        foreach ($subBabs as $idx => $sb) {
            $nomor = $idx + 1;
            $isLocked = ($nomor > 3); // Sub-bab 4 terkunci sesuai mockup
            $isActive = ($nomor === 3); // Sub-bab 3 disorot (active)

            $tipeIcon = match ($nomor) {
                1 => 'cake',
                2 => 'scale',
                3 => 'fraction',
                default => 'lock',
            };

            $subList[] = [
                'id_sub_bab' => $sb->id_sub_bab,
                'nomor' => $nomor,
                'label' => "Sub Bab {$nomor}" . ($isActive ? ' •' : ''),
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
                'info_sub' => count($subList) . ' Sub Bab Pembelajaran',
            ],
            'sub_babs' => $subList,
        ]);
    }

    // GET /api/siswa/sub-bab/{id}/materi
    public function materiList(Request $request, $id)
    {
        $subBab = SubBab::with(['bab.mataPelajaran'])->find($id);
        if (!$subBab) {
            return $this->error('Sub Bab tidak ditemukan', 404);
        }

        $materis = Materi::where('id_sub_bab', $id)->orderBy('id_materi')->get();

        $materiList = [];
        foreach ($materis as $idx => $m) {
            $nomor = $idx + 1;
            $tipe = $m->tipe_materi ?: 'dokumen';

            $materiList[] = [
                'id_materi' => $m->id_materi,
                'nomor' => $nomor,
                'tipe_materi' => $tipe,
                'badge' => "MATERI {$nomor}",
                'info_meta' => $tipe === 'video' ? 'Video Belajar' : ($tipe === 'kuis' ? 'Kuis Mandiri' : 'Dokumen Bacaan'),
                'judul_materi' => $m->judul_materi ?: 'Materi Pembelajaran',
                'deskripsi' => $m->isi_materi ? substr(strip_tags($m->isi_materi), 0, 140) : 'Silakan buka materi untuk mempelajari topik ini.',
                'warna' => ($nomor % 2 == 0) ? 'orange' : 'blue',
                'url_video' => $m->url_video,
                'has_thumbnail' => !empty($m->file_pdf),
            ];
        }

        $babNama = $subBab->bab?->nama_bab ?? 'Bab';
        $subNama = $subBab->nama_sub_bab ?? 'Sub Bab';

        return $this->success([
            'breadcrumb' => "{$babNama} > {$subNama}",
            'sub_bab' => [
                'id_sub_bab' => $subBab->id_sub_bab,
                'nama' => $subBab->nama_sub_bab,
            ],
            'materis' => $materiList,
        ]);
    }

    // GET /api/siswa/materi/{id}
    public function materiDetail(Request $request, $id)
    {
        $materi = Materi::with(['subBab.bab.mataPelajaran'])->find($id);
        if (!$materi) {
            return $this->error('Materi belum diunggah atau tidak ditemukan di database', 404);
        }

        $subBab = $materi->subBab;
        $bab = $subBab?->bab;
        $mapel = $bab?->mataPelajaran;

        return $this->success([
            'id_materi' => $materi->id_materi,
            'breadcrumb' => [
                'mapel' => $mapel?->nama_mapel ?? 'Mata Pelajaran',
                'bab' => $bab?->nama_bab ?? 'Bab',
                'sub_bab' => $subBab?->nama_sub_bab ?? 'Sub Bab',
            ],
            'video' => [
                'judul' => $materi->judul_materi,
                'durasi' => '05:00',
                'kualitas' => 'HD 720p',
                'progres_detik' => 0,
                'total_detik' => 300,
                'url' => $materi->url_video ?? 'https://www.youtube.com',
            ],
            'konten' => [
                'badge' => 'Materi Pembelajaran',
                'judul' => $materi->judul_materi,
                'subjudul' => 'Pelajari materi berikut dengan seksama!',
                'paragraf_1' => strip_tags($materi->isi_materi ?: 'Belum ada konten tertulis untuk materi ini.'),
                'paragraf_2' => '',
                'poin_pembagi' => [],
                'contoh_sekitar' => '',
            ],
            'tips' => [
                'Baca materi dengan teliti sebelum mengerjakan kuis atau latihan!',
            ],
            'kuis_lanjutan' => $materi->id_quiz ? [
                'id_quiz' => $materi->id_quiz,
                'judul' => 'Lanjut ke Kuis / Latihan',
                'jumlah_soal' => Quiz::find($materi->id_quiz)?->soal()->count() ?? 5,
            ] : null,
        ]);
    }

    // GET /api/siswa/quiz/{id?}
    public function quizDetail(Request $request, $id = null)
    {
        $quiz = null;
        if ($id) {
            $quiz = Quiz::with(['mataPelajaran', 'soal'])->find($id);
        }
        if (!$quiz) {
            $quiz = Quiz::with(['mataPelajaran', 'soal'])->first();
        }

        if (!$quiz) {
            return $this->error('Kuis belum tersedia di database', 404);
        }

        $soals = [];
        foreach ($quiz->soal as $index => $s) {
            $metaGambar = null;
            if ($s->gambar) {
                $metaGambar = json_decode($s->gambar, true);
            }
            $tips = $metaGambar['tips'] ?? 'Pilih jawaban yang paling tepat sesuai materi yang telah kamu pelajari.';

            $soals[] = [
                'id_soal' => $s->id_soal,
                'nomor' => $index + 1,
                'pertanyaan' => $s->pertanyaan,
                'ilustrasi' => $metaGambar['diagram'] ?? $metaGambar['ilustrasi'] ?? null,
                'opsi_a' => $s->opsi_a,
                'opsi_b' => $s->opsi_b,
                'opsi_c' => $s->opsi_c,
                'opsi_d' => $s->opsi_d,
                'kunci_jawaban' => $s->kunci_jawaban,
                'tips' => $tips,
            ];
        }

        return $this->success([
            'id_quiz' => $quiz->id_quiz,
            'judul_quiz' => $quiz->judul_quiz,
            'sub_judul' => $quiz->mataPelajaran?->nama_mapel ?? 'Kuis Mandiri',
            'durasi_menit' => $quiz->durasi_menit ?? 15,
            'durasi_detik' => ($quiz->durasi_menit ?? 15) * 60,
            'countdown_display' => sprintf('%02d:00', $quiz->durasi_menit ?? 15),
            'total_soal' => count($soals),
            'soal' => $soals,
        ]);
    }

    // POST /api/siswa/quiz/{id}/submit
    public function quizSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('soal')->find($id);
        if (!$quiz) {
            return $this->error('Kuis tidak ditemukan', 404);
        }

        $jawaban = $request->input('jawaban', []); // misal: {"23": "B", "24": "A", ...}
        $idSiswa = $request->input('id_siswa', 1);

        $totalSoal = $quiz->soal->count();
        $benar = 0;
        $salah = 0;

        foreach ($quiz->soal as $s) {
            $jawab = strtoupper(trim($jawaban[$s->id_soal] ?? $jawaban[(string)$s->id_soal] ?? ''));
            if ($jawab && $jawab === strtoupper(trim($s->kunci_jawaban))) {
                $benar++;
            } else {
                $salah++;
            }
        }

        $nilai = $totalSoal > 0 ? round(($benar / $totalSoal) * 100) : 0;
        $bintang = ($nilai >= 80) ? 3 : (($nilai >= 60) ? 2 : 1);

        // Simpan ke riwayat kuis
        HasilKuisSiswa::create([
            'id_quiz' => $quiz->id_quiz,
            'id_siswa' => $idSiswa,
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'nilai_akhir' => $nilai,
            'waktu_menit' => $request->input('waktu_menit', 3),
            'status_kirim' => 1,
            'waktu_kirim' => Carbon::now(),
        ]);

        return $this->success([
            'nilai_akhir' => $nilai,
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'total_soal' => $totalSoal,
            'bintang' => $bintang,
            'pesan' => $nilai >= 80 ? 'Luar biasa! Kamu memahami materi dengan sangat baik!' : 'Bagus! Terus berlatih agar semakin hebat!',
        ], 'Kuis berhasil dikumpulkan!');
    }

    // =========================================================================
    // FITUR UJIAN ONLINE RESMI (DAFTAR, PETUNJUK, PENGERJAAN, SUBMIT, HASIL)
    // 100% DINAMIS SESUAI DATABASE (Quiz WHERE id_sub_bab IS NULL)
    // =========================================================================

    // GET /api/siswa/ujian
    public function ujianIndex(Request $request)
    {
        $siswaId = $request->query('id_siswa', 1);

        // Ambil seluruh ujian resmi dari database
        $allQuizzes = Quiz::whereNull('id_sub_bab')
            ->with([
                'mataPelajaran',
                'guru',
                'soal',
                'hasilSiswa' => function ($q) use ($siswaId) {
                    if ($siswaId) {
                        $q->where('id_siswa', $siswaId);
                    }
                },
            ])->latest('id_quiz')->get();

        $ujianTersedia = [];
        $ujianSelesai = [];

        foreach ($allQuizzes as $q) {
            $hasil = $q->hasilSiswa->first();
            $mapelNama = $q->mataPelajaran?->nama_mapel ?? 'Umum';
            $soalCount = $q->soal->count();

            if (!$hasil) {
                // Belum dikerjakan -> Masuk Tersedia
                $ujianTersedia[] = [
                    'id_quiz' => $q->id_quiz,
                    'judul_quiz' => $q->judul_quiz,
                    'sub_judul' => $q->deskripsi,
                    'mapel' => $mapelNama,
                    'guru' => $q->guru?->nama_guru ?? 'Guru Pengampu',
                    'durasi_menit' => $q->durasi_menit ?? 60,
                    'total_soal' => $soalCount,
                    'tipe_soal' => 'Pilihan Ganda',
                    'badge_mapel' => $mapelNama,
                    'badge_status' => $soalCount > 0 ? 'Bisa Dikerjakan' : 'Belum Ada Soal',
                    'badge_jadwal' => 'Hari Ini',
                    'is_bisa_dikerjakan' => ($soalCount > 0),
                    'keterangan_tombol' => ($soalCount > 0 ? 'Mulai Ujian' : 'Belum Ada Soal'),
                ];
            } else {
                // Sudah dikerjakan -> Masuk Selesai
                $nilai = (int) $hasil->nilai_akhir;
                $ujianSelesai[] = [
                    'id_quiz' => $q->id_quiz,
                    'id_hasil' => $hasil->id_hasil,
                    'judul_quiz' => $q->judul_quiz,
                    'mapel' => $mapelNama,
                    'tanggal' => $hasil->created_at?->format('d M') ?? 'Baru saja',
                    'keterangan' => $hasil->status_kirim ? 'Selesai' : 'Terkumpul (Menunggu Nilai)',
                    'nilai' => $nilai,
                    'badge_nilai' => $hasil->status_kirim ? "★ {$nilai} – Selesai" : 'Menunggu Nilai',
                ];
            }
        }

        // Nilai Ujian Terakhir (hanya yang sudah berstatus_kirim)
        $latestHasil = HasilKuisSiswa::with(['quiz.mataPelajaran'])
            ->whereHas('quiz', function ($q) {
                $q->whereNull('id_sub_bab');
            })
            ->where('id_siswa', $siswaId)
            ->where('status_kirim', true)
            ->latest('id_hasil')
            ->first();

        $nilaiTerakhirData = null;
        if ($latestHasil && $latestHasil->quiz) {
            $nilaiAkhir = (int) $latestHasil->nilai_akhir;
            $nilaiTerakhirData = [
                'id_quiz' => $latestHasil->id_quiz,
                'mapel' => $latestHasil->quiz->mataPelajaran?->nama_mapel ?? 'Umum',
                'judul' => $latestHasil->quiz->judul_quiz,
                'tanggal' => ($latestHasil->created_at?->format('d M') ?? '') . ' • Selesai',
                'nilai' => $nilaiAkhir,
                'badge' => "{$nilaiAkhir} – " . ($nilaiAkhir >= 75 ? 'Lulus' : 'Remedial'),
            ];
        }

        return $this->success([
            'banner' => [
                'judul' => 'Penilaian Semester Siswa',
                'subjudul' => 'Baca soal dengan teliti dan jangan lupa berdoa ya!',
            ],
            'tersedia_count' => count($ujianTersedia),
            'selesai_count' => count($ujianSelesai),
            'ujian_tersedia' => $ujianTersedia,
            'ujian_selesai' => $ujianSelesai,
            'nilai_terakhir' => $nilaiTerakhirData,
            'tips' => 'Kerjakan soal yang mudah dulu ya, agar waktumu cukup dan nilaimu maksimal!',
        ]);
    }

    // GET /api/siswa/ujian/{id}/petunjuk
    public function ujianPetunjuk(Request $request, $id)
    {
        $quiz = Quiz::with(['guru', 'mataPelajaran', 'soal'])->find($id);
        if (!$quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }

        return $this->success([
            'id_quiz' => (int) $id,
            'kategori_badge' => strtoupper($quiz->tingkat_level ?? 'Ujian Online'),
            'judul_quiz' => $quiz->judul_quiz,
            'guru' => $quiz->guru?->nama_guru ?? 'Guru Pengampu',
            'ringkasan' => [
                'waktu_menit' => $quiz->durasi_menit ?? 60,
                'total_soal' => $quiz->soal->count(),
                'target_kkm' => 75,
                'koreksi' => 'Otomatis',
            ],
            'petunjuk_pengerjaan' => [
                ['nomor' => 1, 'warna' => 'blue', 'teks' => 'Berdoa sebelum mulai.'],
                ['nomor' => 2, 'warna' => 'orange', 'teks' => 'Pilih salah satu jawaban A, B, C, atau D.'],
                ['nomor' => 3, 'warna' => 'green', 'teks' => 'Periksa kembali lalu kumpulkan.'],
            ],
            'tips_semangat' => [
                'judul' => 'Tips Semangat Belajar',
                'pesan' => 'Tenang, teliti, dan jangan lupa berdoa ya!',
            ],
            'footer' => 'Ada kendala saat membuka soal? Beritahu guru pengawasmu',
        ]);
    }

    // GET /api/siswa/ujian/{id}/play
    public function ujianPlay(Request $request, $id)
    {
        $quiz = Quiz::with(['mataPelajaran', 'soal'])->find($id);
        if (!$quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }
        if ($quiz->soal->isEmpty()) {
            return $this->error('Guru belum menginput butir soal untuk ujian ini.', 400);
        }

        $soals = [];
        foreach ($quiz->soal as $index => $s) {
            $meta = [];
            if ($s->gambar) {
                $meta = json_decode($s->gambar, true) ?: [];
            }

            $soals[] = [
                'id_soal' => $s->id_soal,
                'nomor' => $index + 1,
                'tag' => $meta['tag'] ?? ($quiz->mataPelajaran?->nama_mapel ?? 'Soal Ujian'),
                'poin' => $s->bobot_nilai ?? 10,
                'pertanyaan' => $s->pertanyaan,
                'diagram' => $meta['diagram'] ?? null,
                'foto' => $meta['foto'] ?? null,
                'opsi_a' => $s->opsi_a,
                'opsi_b' => $s->opsi_b,
                'opsi_c' => $s->opsi_c,
                'opsi_d' => $s->opsi_d,
                'kunci_jawaban' => $s->kunci_jawaban,
            ];
        }

        $durasiMenit = $quiz->durasi_menit ?? 60;
        return $this->success([
            'id_quiz' => (int) $id,
            'judul_quiz' => $quiz->judul_quiz,
            'durasi_menit' => $durasiMenit,
            'durasi_detik' => $durasiMenit * 60,
            'countdown_display' => sprintf('%02d:00', $durasiMenit),
            'sisa_detik' => $durasiMenit * 60,
            'total_soal' => count($soals),
            'default_index' => 0,
            'soal' => $soals,
        ]);
    }

    // POST /api/siswa/ujian/{id}/submit
    public function ujianSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('soal')->find($id);
        if (!$quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }

        $jawaban = $request->input('jawaban', []); // map: {"id_soal": "B"}
        $idSiswa = $request->input('id_siswa', 1);

        $totalSoal = $quiz->soal->count();
        $benar = 0;
        $salah = 0;

        foreach ($quiz->soal as $s) {
            $jawab = strtoupper(trim($jawaban[$s->id_soal] ?? $jawaban[(string)$s->id_soal] ?? ''));
            if ($jawab && $jawab === strtoupper(trim($s->kunci_jawaban))) {
                $benar++;
            } else {
                $salah++;
            }
        }

        $nilai = $totalSoal > 0 ? round(($benar / $totalSoal) * 100) : 0;
        $waktuMenit = $request->input('waktu_menit', 10);

        // Simpan / update hasil ujian
        HasilKuisSiswa::updateOrCreate(
            [
                'id_quiz' => (int) $id,
                'id_siswa' => $idSiswa,
            ],
            [
                'jumlah_benar' => $benar,
                'jumlah_salah' => $salah,
                'nilai_akhir' => $nilai,
                'waktu_menit' => $waktuMenit,
                'status_kirim' => 1,
                'catatan_guru' => $nilai >= 75 ? 'Hasil ujian sangat baik, pertahankan!' : 'Perlu belajar lebih giat lagi.',
                'waktu_kirim' => Carbon::now(),
            ]
        );

        return $this->success([
            'id_quiz' => (int) $id,
            'nilai_akhir' => $nilai,
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'durasi_menit' => $waktuMenit,
            'is_lulus_kkm' => ($nilai >= 75),
            'target_kkm' => 75,
        ], 'Ujian berhasil dikumpulkan tepat waktu!');
    }

    // GET /api/siswa/ujian/{id}/result
    public function ujianResult(Request $request, $id)
    {
        $quiz = Quiz::with(['guru', 'mataPelajaran'])->find($id);
        $siswaId = $request->query('id_siswa', 1);
        $siswa = Siswa::find($siswaId);

        $hasil = HasilKuisSiswa::where('id_quiz', $id)
            ->where('id_siswa', $siswaId)
            ->latest('id_hasil')
            ->first();

        if (!$hasil) {
            return $this->error('Hasil ujian belum ditemukan untuk siswa ini', 404);
        }

        $nilai = (int) $hasil->nilai_akhir;
        $benar = $hasil->jumlah_benar;
        $salah = $hasil->jumlah_salah;
        $waktu = $hasil->waktu_menit ?? 10;

        return $this->success([
            'id_quiz' => (int) $id,
            'nama_siswa' => $siswa?->nama_siswa ?? 'Siswa',
            'pesan_banner' => 'Ujian telah selesai dikumpulkan!',
            'target_kkm' => 75,
            'is_lulus_kkm' => ($nilai >= 75),
            'label_kkm' => ($nilai >= 75) ? 'LULUS KKM (Target: 75)' : 'BELUM LULUS (Target: 75)',
            'nilai_akhir' => $nilai,
            'status_teks' => $nilai >= 85 ? 'Sangat Baik & Membanggakan' : ($nilai >= 75 ? 'Lulus & Memuaskan' : 'Perlu Remedial'),
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'durasi_menit' => $waktu,
            'mapel' => [
                'nama' => $quiz?->judul_quiz ?? 'Ujian Online',
                'guru' => $quiz?->guru?->nama_guru ?? 'Guru Pengampu',
                'catatan_guru' => $hasil->catatan_guru ?? 'Tetap semangat dalam belajar!',
            ],
        ]);
    }
}
