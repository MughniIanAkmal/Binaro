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
                'sub_bab_count' => $b->subBab()->count() ?: 4,
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
                'materi_count' => $sb->materi()->count() ?: 3,
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
        $subBab = SubBab::with(['bab.mataPelajaran', 'materi'])->find($id);
        if (!$subBab) {
            return $this->error('Sub Bab tidak ditemukan', 404);
        }

        $materis = Materi::where('id_sub_bab', $id)->orderBy('id_materi')->get();

        // Template materi default sesuai mockup jika kosong
        $materiList = [];
        foreach ($materis as $idx => $m) {
            $nomor = $idx + 1;
            $tipe = $m->tipe_materi;

            $badge = "MATERI {$nomor}";
            $meta = '5 Menit';
            $warna = 'blue';

            if ($tipe === 'video' || $nomor === 1) {
                $meta = '5 Menit';
                $warna = 'blue';
            } elseif ($tipe === 'dokumen' || $nomor === 2) {
                $meta = 'Hal 2 / 6';
                $warna = 'orange';
            } elseif ($tipe === 'kuis' || $nomor === 3) {
                $meta = '8 Soal Cerita';
                $warna = 'blue';
            }

            $materiList[] = [
                'id_materi' => $m->id_materi,
                'nomor' => $nomor,
                'tipe_materi' => $tipe,
                'badge' => $badge,
                'info_meta' => $meta,
                'judul_materi' => $m->judul_materi ?: 'Pecahan dan Desimal Interaktif',
                'deskripsi' => $m->isi_materi ? substr(strip_tags($m->isi_materi), 0, 120) : 'Pembelajaran interaktif materi pecahan SD.',
                'warna' => $warna,
                'url_video' => $m->url_video,
                'has_thumbnail' => ($nomor === 2),
            ];
        }

        return $this->success([
            'breadcrumb' => 'Bab 1 > Sub Bab 3',
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
            // Ambil materi pertama sebagai fallback
            $materi = Materi::with(['subBab.bab.mataPelajaran'])->first();
        }

        return $this->success([
            'id_materi' => $materi ? $materi->id_materi : 1,
            'breadcrumb' => [
                'mapel' => 'Matematika',
                'bab' => 'Bab 1 Pecahan',
                'sub_bab' => 'Sub Bab 3 Pecahan 1/2',
            ],
            'video' => [
                'judul' => 'Animasi Seru: Membagi Buah Menjadi Dua Sama Besar',
                'durasi' => '04:25',
                'kualitas' => 'HD 720p',
                'progres_detik' => 75, // 01:15
                'total_detik' => 265, // 04:25
                'url' => $materi?->url_video ?? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            'ilustrasi' => [
                'judul' => 'Ilustrasi Konsep 1/2',
                'caption' => 'ONE WHOLE APPLE = TWO EQUAL HALVES',
            ],
            'konten' => [
                'badge' => 'Materi Dasar',
                'judul' => 'Mengenal Pecahan 1/2 (Setengah)',
                'subjudul' => 'Belajar membagi benda secara adil dan sama besar!',
                'paragraf_1' => 'Bayangkan kamu memiliki satu buah apel manis yang utuh. Kemudian, adikmu juga ingin mencicipi apel tersebut. Agar adil, kamu membelah apel menjadi dua bagian yang ukurannya persis sama besar.',
                'paragraf_2' => 'Setiap potongan apel itu disebut satu perdua atau setengah, dan dalam matematika ditulis sebagai 1/2.',
                'poin_pembagi' => [
                    [
                        'nomor' => '1',
                        'warna' => 'blue',
                        'judul' => 'Angka 1 (Atas) = Pembilang',
                        'keterangan' => 'Banyaknya bagian apel yang kamu ambil (1 potong).',
                    ],
                    [
                        'nomor' => '2',
                        'warna' => 'orange',
                        'judul' => 'Angka 2 (Bawah) = Penyebut',
                        'keterangan' => 'Total seluruh bagian apel setelah dipotong (2 potong sama besar).',
                    ],
                ],
                'contoh_sekitar' => 'Selain apel, kita juga bisa membagi pizza, kue bolu, atau selembar kertas lipat menjadi dua bagian yang sama besar. Jika kedua bagian tidak sama besar, maka itu belum bisa disebut pecahan 1/2 ya!',
            ],
            'tips' => [
                'Kuncinya adalah HARUS SAMA BESAR. Jika satu potongan lebih besar dari yang lain, itu bukan pecahan 1/2.',
                '1/2 + 1/2 akan kembali menjadi 1 bagian utuh!',
            ],
            'kuis_lanjutan' => [
                'id_kuis' => 11,
                'judul' => 'Lanjut ke Kuis / Latihan (5 Soal)',
                'jumlah_soal' => 5,
            ],
        ]);
    }

    // GET /api/siswa/quiz/{id?}
    public function quizDetail(Request $request, $id = null)
    {
        $quiz = null;
        if ($id) {
            $quiz = Quiz::with('soal')->find($id);
        }
        if (!$quiz) {
            $quiz = Quiz::where('judul_quiz', 'like', '%Pecahan Sederhana%')->with('soal')->first()
                ?? Quiz::with('soal')->first();
        }

        if (!$quiz) {
            return $this->error('Kuis tidak ditemukan', 404);
        }

        $soals = [];
        foreach ($quiz->soal as $index => $s) {
            $metaGambar = null;
            if ($s->gambar) {
                $metaGambar = json_decode($s->gambar, true);
            }
            $tips = $metaGambar['tips'] ?? 'Pilih jawaban yang paling tepat sesuai materi yang telah kamu pelajari.';
            $ilustrasi = null;
            if (isset($metaGambar['tipe']) && $metaGambar['tipe'] === 'semangka_4') {
                $ilustrasi = [
                    'tipe' => 'semangka',
                    'total_bagian' => $metaGambar['total_bagian'] ?? 4,
                    'bagian_diambil' => $metaGambar['bagian_diambil'] ?? 1,
                    'caption' => $metaGambar['caption'] ?? '1 dari 4 potongan semangka diambil oleh Budi',
                ];
            }

            $soals[] = [
                'id_soal' => $s->id_soal,
                'nomor' => $index + 1,
                'pertanyaan' => $s->pertanyaan,
                'ilustrasi' => $ilustrasi,
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
            'sub_judul' => 'Bab 3',
            'durasi_menit' => $quiz->durasi_menit ?? 15,
            'durasi_detik' => ($quiz->durasi_menit ?? 15) * 60,
            'countdown_display' => '12:44',
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
            'pesan' => $nilai >= 80 ? 'Luar biasa! Kamu memahami konsep pecahan dengan sangat baik!' : 'Bagus! Terus berlatih agar semakin hebat!',
        ], 'Kuis berhasil dikumpulkan!');
    }

    // =========================================================================
    // FITUR UJIAN ONLINE RESMI (DAFTAR, PETUNJUK, PENGERJAAN, SUBMIT, HASIL)
    // =========================================================================

    // GET /api/siswa/ujian
    public function ujianIndex(Request $request)
    {
        $siswaId = $request->query('id_siswa', 1);

        // Ujian Tersedia (Screenshot 1)
        $ujianTersedia = [
            [
                'id_quiz' => 12,
                'judul_quiz' => 'Penilaian Tengah Semester: Matematika Kelas 4B',
                'mapel' => 'Matematika',
                'guru' => 'Ibu Sarah Wijaya, S.Pd.',
                'durasi_menit' => 60,
                'total_soal' => 20,
                'tipe_soal' => 'Pilihan Ganda',
                'badge_mapel' => 'Matematika',
                'badge_status' => 'Bisa Dikerjakan',
                'badge_jadwal' => 'Hari Ini',
                'is_bisa_dikerjakan' => true,
                'keterangan_tombol' => 'Mulai Ujian',
            ],
            [
                'id_quiz' => 13,
                'judul_quiz' => 'Kuis Harian: IPAS – Daur Hidup Hewan',
                'sub_judul' => 'Materi Metamorfosis Sempurna & Tidak Sempurna',
                'mapel' => 'IPAS',
                'guru' => 'Bapak Rahmat Hidayat, M.Pd.',
                'durasi_menit' => 45,
                'total_soal' => 15,
                'tipe_soal' => 'Kuis',
                'badge_mapel' => 'IPAS',
                'badge_status' => 'Belum Dimulai',
                'badge_jadwal' => 'Besok, 09.00 WIB',
                'is_bisa_dikerjakan' => false,
                'keterangan_tombol' => 'Dibuka Besok Jam 09.00',
            ],
        ];

        // Ujian Selesai
        $ujianSelesai = [
            [
                'id_quiz' => 14,
                'id_hasil' => 8,
                'judul_quiz' => 'Ulangan Harian Puisi A',
                'mapel' => 'Bahasa Indonesia',
                'tanggal' => '12 Mei',
                'keterangan' => 'Sangat Memuaskan!',
                'nilai' => 95,
                'badge_nilai' => '95 – Hebat!',
            ],
            [
                'id_quiz' => 15,
                'id_hasil' => 9,
                'judul_quiz' => 'Penilaian Harian: Tata Surya',
                'mapel' => 'IPAS',
                'tanggal' => '5 Mei',
                'keterangan' => 'Lulus KKM',
                'nilai' => 88,
                'badge_nilai' => '88 – Bagus!',
            ],
            [
                'id_quiz' => 16,
                'id_hasil' => 10,
                'judul_quiz' => 'Kuis Pancasila & Norma',
                'mapel' => 'PPKn',
                'tanggal' => '28 April',
                'keterangan' => 'Lulus KKM',
                'nilai' => 85,
                'badge_nilai' => '85 – Bagus!',
            ],
        ];

        return $this->success([
            'banner' => [
                'judul' => 'Penilaian Semester Siswa',
                'subjudul' => 'Baca soal dengan teliti dan jangan lupa berdoa ya!',
            ],
            'tersedia_count' => count($ujianTersedia),
            'selesai_count' => count($ujianSelesai),
            'ujian_tersedia' => $ujianTersedia,
            'ujian_selesai' => $ujianSelesai,
            'nilai_terakhir' => [
                'id_quiz' => 14,
                'mapel' => 'Bahasa Indonesia',
                'judul' => 'Ulangan Harian Puisi A',
                'tanggal' => '12 Mei • Sangat Memuaskan!',
                'nilai' => 95,
                'badge' => '95 – Hebat!',
            ],
            'tips' => 'Kerjakan soal yang mudah dulu ya, agar waktumu cukup dan nilaimu maksimal!',
        ]);
    }

    // GET /api/siswa/ujian/{id}/petunjuk
    public function ujianPetunjuk(Request $request, $id)
    {
        $quiz = Quiz::with('guru')->find($id);

        return $this->success([
            'id_quiz' => (int) $id,
            'kategori_badge' => 'UJIAN TENGAH SEMESTER',
            'judul_quiz' => $quiz?->judul_quiz ?? 'PTS Matematika Kelas 4B',
            'guru' => $quiz?->guru?->nama_guru ?? 'Ibu Sarah Wijaya, S.Pd.',
            'ringkasan' => [
                'waktu_menit' => $quiz?->durasi_menit ?? 60,
                'total_soal' => 20,
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
        $quiz = Quiz::with('soal')->find($id);
        if (!$quiz) {
            $quiz = Quiz::where('judul_quiz', 'like', '%PTS%')->with('soal')->first() ?? Quiz::first();
        }

        $soals = [];
        if ($quiz && $quiz->soal) {
            foreach ($quiz->soal as $index => $s) {
                $meta = [];
                if ($s->gambar) {
                    $meta = json_decode($s->gambar, true) ?: [];
                }

                $nomor = $index + 1;
                $diagram = null;
                $foto = null;

                // Format khusus Soal nomor 5 (Semangka) sesuai screenshot
                if ($nomor === 5 || isset($meta['diagram'])) {
                    $diagram = [
                        'tipe' => 'pie_semangka_8',
                        'judul' => 'DIAGRAM PECAHAN 8 POTONG',
                        'dimakan' => 3,
                        'sisa' => 5,
                        'total' => 8,
                        'label_dimakan' => 'Dimakan Budi (3)',
                        'label_sisa' => 'Sisa Semangka (5)',
                    ];
                    $foto = [
                        'caption' => 'Semangka dipotong 8',
                    ];
                }

                $soals[] = [
                    'id_soal' => $s->id_soal,
                    'nomor' => $nomor,
                    'tag' => $meta['tag'] ?? 'Pecahan Sederhana',
                    'poin' => $meta['poin'] ?? 5,
                    'pertanyaan' => $s->pertanyaan,
                    'diagram' => $diagram,
                    'foto' => $foto,
                    'opsi_a' => $s->opsi_a,
                    'opsi_b' => $s->opsi_b,
                    'opsi_c' => $s->opsi_c,
                    'opsi_d' => $s->opsi_d,
                    'kunci_jawaban' => $s->kunci_jawaban,
                ];
            }
        }

        return $this->success([
            'id_quiz' => (int) $id,
            'judul_quiz' => 'PTS Matematika',
            'durasi_menit' => $quiz?->durasi_menit ?? 60,
            'durasi_detik' => ($quiz?->durasi_menit ?? 60) * 60,
            'countdown_display' => '48:14',
            'sisa_detik' => 2894, // 48 menit 14 detik sesuai screenshot
            'total_soal' => count($soals),
            'default_index' => 4, // Soal ke-5 (index 4) aktif sesuai screenshot
            'soal' => $soals,
        ]);
    }

    // POST /api/siswa/ujian/{id}/submit
    public function ujianSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('soal')->find($id);
        $jawaban = $request->input('jawaban', []); // map: {"id_soal": "B"}
        $idSiswa = $request->input('id_siswa', 1);

        $totalSoal = $quiz ? $quiz->soal->count() : 20;
        $benar = 0;
        $salah = 0;

        if ($quiz && $quiz->soal->isNotEmpty()) {
            foreach ($quiz->soal as $s) {
                $jawab = strtoupper(trim($jawaban[$s->id_soal] ?? $jawaban[(string)$s->id_soal] ?? ''));
                if ($jawab && $jawab === strtoupper(trim($s->kunci_jawaban))) {
                    $benar++;
                } else {
                    $salah++;
                }
            }
        } else {
            $benar = 18;
            $salah = 2;
        }

        $nilai = $totalSoal > 0 ? round(($benar / $totalSoal) * 100) : 90;
        $waktuMenit = $request->input('waktu_menit', 38);

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
                'catatan_guru' => 'Bagus sekali pemahaman materi pecahannya, pertahankan ya Budi!',
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
        $quiz = Quiz::with('guru')->find($id);
        $siswaId = $request->query('id_siswa', 1);
        $siswa = Siswa::find($siswaId);

        $hasil = HasilKuisSiswa::where('id_quiz', $id)
            ->where('id_siswa', $siswaId)
            ->latest('id_hasil')
            ->first();

        $nilai = $hasil ? (int) $hasil->nilai_akhir : 90;
        $benar = $hasil ? $hasil->jumlah_benar : 18;
        $salah = $hasil ? $hasil->jumlah_salah : 2;
        $waktu = $hasil ? ($hasil->waktu_menit ?? 38) : 38;

        return $this->success([
            'id_quiz' => (int) $id,
            'nama_siswa' => $siswa?->nama_siswa ?? 'Budi Santoso',
            'pesan_banner' => 'Ujian telah selesai dikumpulkan tepat waktu!',
            'target_kkm' => 75,
            'is_lulus_kkm' => ($nilai >= 75),
            'label_kkm' => 'LULUS KKM (Target: 75)',
            'nilai_akhir' => $nilai,
            'status_teks' => $nilai >= 85 ? 'Sangat Baik & Membanggakan' : ($nilai >= 75 ? 'Lulus & Memuaskan' : 'Perlu Remedial'),
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'durasi_menit' => $waktu,
            'mapel' => [
                'nama' => 'PTS Matematika Kelas 4B',
                'guru' => $quiz?->guru?->nama_guru ?? 'Ibu Sarah Wijaya, S.Pd.',
                'catatan_guru' => $hasil?->catatan_guru ?? 'Bagus sekali pemahaman materi pecahannya, pertahankan ya Budi!',
            ],
        ]);
    }
}
