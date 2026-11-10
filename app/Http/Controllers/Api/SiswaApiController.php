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

    /**
     * Resolve ID Siswa dari token Sanctum (prioritas utama), lalu fallback
     * parameter/session/first untuk preview web & pengembangan.
     */
    protected function resolveSiswaId(Request $request): ?int
    {
        // 1. User dari token Bearer / actingAs — guard sanctum memvalidasi
        //    personal_access_tokens meski route-nya publik.
        $user = null;
        try {
            if (config('auth.guards.sanctum')) {
                $user = $request->user('sanctum');
            }
        } catch (\Throwable $e) {}
        if (! $user) {
            $user = $request->user();
        }
        if ($user && isset($user->id_siswa)) {
            return (int) $user->id_siswa;
        }

        // 2. Tanpa autentikasi: parameter, session, lalu siswa pertama (dev preview).
        $id = $request->input('id_siswa')
            ?? $request->query('id_siswa')
            ?? $request->query('id')
            ?? Session::get('siswa_id')
            ?? Session::get('user_id');

        if ($id) {
            return (int) $id;
        }

        $first = Siswa::first();
        return $first?->id_siswa;
    }

    // GET /api/siswa/dashboard
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
            
            // Hitung progress riil kuis bab terbuka
            $progData = $this->calculateMapelProgress($m->id_mapel, $siswaId);
            $progress = $progData['progress'];
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
                'persen' => $progData['persen'],
                'progress_text' => $progData['progress_text'],
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
                ? \Carbon\Carbon::parse($pr->tgl_tenggat)->translatedFormat('l, d M H.i') . ' WIB'
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

    // GET /api/siswa/profile
    public function profile(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);

        $siswa = null;
        if ($siswaId) {
            $siswa = Siswa::with(['barcode', 'kelas', 'absens' => function ($query) {
                $query->orderByDesc('tanggal')->orderByDesc('waktu_absen');
            }])->find($siswaId);
        }

        if (!$siswa) {
            $siswa = Siswa::with(['barcode', 'kelas'])->first();
        }

        if (!$siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $barcodeCode = $siswa->barcode?->kode_barcode ?? ('SISWA-' . $siswa->nisn);

        // Hitung absensi bulan ini
        $hadir = $siswa->absens ? $siswa->absens->where('status', 'Hadir')->count() : 0;
        $izin = $siswa->absens ? $siswa->absens->where('status', 'Izin')->count() : 0;
        $sakit = $siswa->absens ? $siswa->absens->where('status', 'Sakit')->count() : 0;
        $alpa = $siswa->absens ? $siswa->absens->where('status', 'Alpa')->count() : 0;

        return $this->success([
            // Kontrak API: kunci flat di level data (dipakai test & mobile),
            // sementara objek "siswa" dipertahankan untuk kompatibilitas aplikasi lama.
            'id_siswa' => $siswa->id_siswa,
            'nisn' => $siswa->nisn,
            'siswa' => [
                'id_siswa' => $siswa->id_siswa,
                'nama' => $siswa->nm_siswa,
                'nm_siswa' => $siswa->nm_siswa,
                'nama_siswa' => $siswa->nm_siswa,
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
        ], 'Data profil siswa berhasil dimuat');
    }

    // POST /api/siswa/update-password
    public function updatePassword(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        $siswa = Siswa::find($siswaId);
        if (!$siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:4',
            'konfirmasi_password' => 'required|string|same:password_baru',
        ]);

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
        $userId = $this->resolveSiswaId($request);
        if (!$userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $siswa = Siswa::find($userId);
        if (!$siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $data = $request->validate([
            'nm_siswa' => 'sometimes|string|max:255',
            'nama' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:100',
            'foto_profil' => 'sometimes|nullable|string',
        ]);

        if (isset($data['nama']) && !isset($data['nm_siswa'])) {
            $data['nm_siswa'] = $data['nama'];
        }

        $siswa->update($data);

        return $this->success($siswa, 'Profil berhasil diperbarui');
    }

    // GET /api/siswa/absensi-hari-ini
    public function todayAttendance(Request $request)
    {
        $userId = $this->resolveSiswaId($request);
        if (!$userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $today = now()->toDateString();
        $absen = Absen::where('id_siswa', $userId)
            ->where('tanggal', $today)
            ->first();

        return $this->success([
            'id_siswa' => $userId,
            'tanggal' => $today,
            'status' => $absen ? $absen->status : 'Belum Hadir',
            'keterangan' => $absen ? ($absen->keterangan ?? 'Tepat Waktu') : 'Belum melakukan absensi hari ini',
            'waktu_absen' => $absen?->waktu_absen,
            'metode' => $absen?->metode,
            'detail' => $absen,
        ], 'Data absensi hari ini berhasil dimuat');
    }

    // POST /api/siswa/absensi-scan
    public function selfScan(Request $request)
    {
        $userId = $this->resolveSiswaId($request);
        if (!$userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $kode = null;
        if ($request->filled('kode_barcode')) {
            $kode = $request->input('kode_barcode');
        } elseif ($request->filled('id_barcode')) {
            $bc = DB::table('barcode')->where('id_barcode', $request->input('id_barcode'))->first();
            $kode = $bc?->kode_barcode;
        } else {
            $bc = DB::table('barcode')->where('id_siswa', $userId)->first();
            $kode = $bc?->kode_barcode;
        }

        if (!$kode) {
            return $this->error('Barcode tidak valid atau belum terdaftar', 404);
        }

        $hasil = $this->absensiService->catatScan($kode, null, 'scan_qr');

        if ($hasil['hasil'] === 'duplikat') {
            return $this->error($hasil['message'], 409);
        }

        if ($hasil['hasil'] === 'tidak_dikenal') {
            return $this->error('QR Barcode tidak dikenali oleh sistem', 404);
        }

        $siswa = Siswa::find($userId);

        return $this->success([
            'siswa' => $siswa?->nm_siswa ?? 'Siswa',
            'nisn' => $siswa?->nisn,
            'status' => 'Hadir',
            'keterangan' => $hasil['keterangan'] ?? 'Hadir Tepat Waktu',
            'waktu_absen' => $hasil['absen']->waktu_absen,
            'metode' => 'scan_qr',
        ], $hasil['upgrade'] ? 'Absensi diperbarui dari status Alpa' : 'Absensi registrasi berhasil', 201);
    }

    // =========================================================================
    // FITUR MAPEL, BAB, SUB-BAB, DAN MATERI PEMBELAJARAN
    // =========================================================================

    // GET /api/siswa/mapel
    public function mapelList(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request) ?? 1;
        $daftarMapel = MataPelajaran::withCount('bab')->orderBy('id_mapel')->get();

        $result = [];
        foreach ($daftarMapel as $m) {
            $name = $m->nama_mapel;
            $lower = strtolower($name);

            $icon = 'book';
            $warna = 'blue';
            $jadwal = 'Senin & Rabu';
            $wajib = true;
            $babTotal = $m->bab_count;
            $babRentang = $babTotal > 0 ? "Bab 1 - {$babTotal}" : "Belum Ada Bab";

            // Hitung progress riil berdasarkan kuis di bab yang terbuka
            $progData = $this->calculateMapelProgress($m->id_mapel, $siswaId);
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
            'kelas' => 'Mata Pelajaran Kelas 5 SD • Semester 2',
            'total_mapel' => count($result),
            'wajib_count' => count(array_filter($result, fn($x) => $x['is_wajib'])),
            'mapel' => $result,
        ], 'Daftar mata pelajaran berhasil dimuat');
    }

    // GET /api/siswa/mapel/{id}/bab
    public function babList(Request $request, $id)
    {
        $mapel = MataPelajaran::with(['bab.subBab'])->find($id);
        if (!$mapel) {
            $mapel = MataPelajaran::with(['bab.subBab'])->whereHas('bab')->first()
                ?? MataPelajaran::first();
        }

        if (!$mapel) {
            return $this->error('Mata pelajaran tidak ditemukan', 404);
        }

        $babs = Bab::where('id_mapel', $mapel->id_mapel)->orderBy('id_bab')->get();
        $isMatematika = str_contains(strtolower($mapel->nama_mapel), 'matematika');

        $babList = [];
        $unlockedCount = 0;
        foreach ($babs as $index => $b) {
            $nomor = $index + 1;
            $isLocked = (bool) ($b->is_locked ?? false);
            if (!$isLocked) $unlockedCount++;

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
        ], 'Daftar bab berhasil dimuat');
    }

    // GET /api/siswa/bab/{id}/sub-bab
    public function subBabList(Request $request, $id)
    {
        $bab = Bab::with('mataPelajaran')->find($id);
        if (!$bab) {
            $bab = Bab::with('mataPelajaran')->whereHas('subBab')->first()
                ?? Bab::with('mataPelajaran')->first();
        }

        if (!$bab) {
            return $this->error('Bab tidak ditemukan', 404);
        }

        $subBabs = SubBab::where('id_bab', $bab->id_bab)->orderBy('id_sub_bab')->get();

        $subList = [];
        foreach ($subBabs as $idx => $sb) {
            $nomor = $idx + 1;
            $isLocked = (bool) ($sb->is_locked ?? false);
            $isActive = !$isLocked;

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
        ], 'Daftar sub bab berhasil dimuat');
    }

    // GET /api/siswa/sub-bab/{id}/materi
    public function materiList(Request $request, $id)
    {
        $subBab = SubBab::with(['bab.mataPelajaran'])->find($id);
        if (!$subBab) {
            $subBab = SubBab::with(['bab.mataPelajaran'])->whereHas('materi')->first()
                ?? SubBab::with(['bab.mataPelajaran'])->first();
        }

        if (!$subBab) {
            return $this->error('Sub Bab tidak ditemukan', 404);
        }

        $materis = Materi::where('id_sub_bab', $subBab->id_sub_bab)->orderBy('id_materi')->get();

        $materiList = [];
        foreach ($materis as $idx => $m) {
            $nomor = $idx + 1;
            $tipe = $m->tipe_materi ?: 'dokumen';

            $deskripsi = $m->isi_materi ? substr(strip_tags($m->isi_materi), 0, 140) : null;
            if (!$deskripsi) {
                $deskripsi = match ($tipe) {
                    'kuis' => 'Silakan kerjakan quiz untuk menguji pemahaman materi ini.',
                    'video' => 'Tonton video penjelasan materi pembelajaran interaktif ini.',
                    default => 'Pelajari dokumen rangkuman materi pembelajaran ini.',
                };
            }

            $materiList[] = [
                'id_materi' => $m->id_materi,
                'nomor' => $nomor,
                'tipe_materi' => $tipe,
                'id_quiz' => $m->id_quiz,
                'badge' => $tipe === 'kuis' ? "KUIS {$nomor}" : "MATERI {$nomor}",
                'info_meta' => $tipe === 'video' ? 'Video Belajar' : ($tipe === 'kuis' ? 'Kuis Interaktif' : 'Dokumen PDF'),
                'judul_materi' => $m->judul_materi ?: 'Materi Pembelajaran',
                'deskripsi' => $deskripsi,
                'warna' => $tipe === 'kuis' ? 'orange' : (($nomor % 2 == 0) ? 'orange' : 'blue'),
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
        ], 'Daftar materi berhasil dimuat');
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

        $hasVideo = !empty($materi->url_video);
        $hasPdf = !empty($materi->file_pdf);
        $hasText = !empty(trim(strip_tags((string)$materi->isi_materi)));

        $storageUrl = $request->root() . '/storage/' . $materi->file_pdf;
        $downloadUrl = $request->root() . '/storage/' . $materi->file_pdf . '?download=1';

        return $this->success([
            'id_materi' => $materi->id_materi,
            'tipe_materi' => $materi->tipe_materi ?: ($hasPdf ? 'dokumen' : ($hasVideo ? 'video' : 'teks')),
            'judul_materi' => $materi->judul_materi,
            'id_quiz' => $materi->id_quiz,
            'breadcrumb' => [
                'mapel' => $mapel?->nama_mapel ?? 'Mata Pelajaran',
                'bab' => $bab?->nama_bab ?? 'Bab',
                'sub_bab' => $subBab?->nama_sub_bab ?? 'Sub Bab',
            ],
            'video' => $hasVideo ? [
                'judul' => $materi->judul_materi,
                'url' => $materi->youtube_watch_url ?: $materi->url_video,
            ] : null,
            'pdf' => $hasPdf ? [
                'file_pdf' => basename($materi->file_pdf),
                'url' => $storageUrl,
                'download_url' => $downloadUrl,
            ] : null,
            'konten' => $hasText ? [
                'judul' => $materi->judul_materi,
                'isi' => $materi->isi_materi,
            ] : null,
            'kuis_lanjutan' => $materi->id_quiz ? [
                'id_quiz' => $materi->id_quiz,
                'judul' => 'Lanjut ke Kuis / Latihan',
                'jumlah_soal' => Quiz::find($materi->id_quiz)?->soal()->count() ?? 0,
            ] : null,
        ], 'Detail materi berhasil dimuat');
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
        ], 'Data kuis berhasil dimuat');
    }

    // POST /api/siswa/quiz/{id}/submit
    public function quizSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('soal')->find($id);
        if (!$quiz) {
            return $this->error('Kuis tidak ditemukan', 404);
        }

        $jawaban = $request->input('jawaban', []);
        $idSiswa = $this->resolveSiswaId($request) ?? 1;

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

        HasilKuisSiswa::updateOrCreate(
            [
                'id_quiz' => $quiz->id_quiz,
                'id_siswa' => $idSiswa,
            ],
            [
                'jumlah_benar' => $benar,
                'jumlah_salah' => $salah,
                'nilai_akhir' => $nilai,
                'waktu_menit' => $request->input('waktu_menit', 3),
                'status_kirim' => 1,
                'waktu_kirim' => Carbon::now(),
            ]
        );

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
    // FITUR UJIAN ONLINE RESMI
    // =========================================================================

    // GET /api/siswa/ujian
    public function ujianIndex(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);

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
                $isPublished = (bool) ($hasil->status_kirim ?? false);
                $nilai = (int) $hasil->nilai_akhir;
                $ujianSelesai[] = [
                    'id_quiz' => $q->id_quiz,
                    'id_hasil' => $hasil->id_hasil,
                    'judul_quiz' => $q->judul_quiz,
                    'mapel' => $mapelNama,
                    'tanggal' => $hasil->created_at?->format('d M') ?? 'Baru saja',
                    'is_published' => $isPublished,
                    'keterangan' => $isPublished ? 'Selesai' : 'Terkumpul (Menunggu Nilai)',
                    'nilai' => $isPublished ? $nilai : null,
                    'badge_nilai' => $isPublished ? "★ {$nilai}" : 'Menunggu Nilai',
                ];
            }
        }

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
        ], 'Daftar ujian berhasil dimuat');
    }

    // GET /api/siswa/ujian/{id}/petunjuk
    public function ujianPetunjuk(Request $request, $id)
    {
        $quiz = Quiz::with(['guru', 'mataPelajaran', 'soal'])->find($id);
        if (!$quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with(['guru', 'mataPelajaran', 'soal'])->first();
        }

        if (!$quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }

        return $this->success([
            'id_quiz' => (int) $quiz->id_quiz,
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
        ], 'Petunjuk ujian berhasil dimuat');
    }

    // GET /api/siswa/ujian/{id}/play
    public function ujianPlay(Request $request, $id)
    {
        $quiz = Quiz::with(['mataPelajaran', 'soal'])->find($id);
        if (!$quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with(['mataPelajaran', 'soal'])->first();
        }

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
            'id_quiz' => (int) $quiz->id_quiz,
            'judul_quiz' => $quiz->judul_quiz,
            'durasi_menit' => $durasiMenit,
            'durasi_detik' => $durasiMenit * 60,
            'countdown_display' => sprintf('%02d:00', $durasiMenit),
            'sisa_detik' => $durasiMenit * 60,
            'total_soal' => count($soals),
            'default_index' => 0,
            'soal' => $soals,
        ], 'Soal ujian berhasil dimuat');
    }

    // POST /api/siswa/ujian/{id}/submit
    public function ujianSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('soal')->find($id);
        if (!$quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with('soal')->first();
        }

        if (!$quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }

        $jawaban = $request->input('jawaban', []);
        $idSiswa = $this->resolveSiswaId($request) ?? 1;

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

        HasilKuisSiswa::updateOrCreate(
            [
                'id_quiz' => (int) $quiz->id_quiz,
                'id_siswa' => $idSiswa,
            ],
            [
                'jumlah_benar' => $benar,
                'jumlah_salah' => $salah,
                'nilai_akhir' => $nilai,
                'waktu_menit' => $waktuMenit,
                'status_kirim' => 0, // Nilai disembunyikan sampai guru menekan "Tampilkan Nilai ke Seluruh Siswa"
                'catatan_guru' => $nilai >= 75 ? 'Hasil ujian sangat baik, pertahankan!' : 'Perlu belajar lebih giat lagi.',
                'waktu_kirim' => null,
            ]
        );

        return $this->success([
            'id_quiz' => (int) $quiz->id_quiz,
            'is_published' => false,
            'status_publikasi' => 'Menunggu Pengumuman Nilai dari Guru',
            'pesan_banner' => 'Ujian Berhasil Dikumpulkan!',
            'sub_pesan' => 'Jawabanmu telah tersimpan dengan aman. Nilai dan rincian benar/salah akan muncul setelah diumumkan oleh guru.',
            'durasi_menit' => $waktuMenit,
            'target_kkm' => 75,
        ], 'Ujian berhasil dikumpulkan! Menunggu pengumuman nilai dari guru.');
    }

    // GET /api/siswa/ujian/{id}/result
    public function ujianResult(Request $request, $id)
    {
        $quiz = Quiz::with(['guru', 'mataPelajaran'])->find($id);
        if (!$quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with(['guru', 'mataPelajaran'])->first();
        }

        $siswaId = $this->resolveSiswaId($request) ?? 1;
        $siswa = Siswa::find($siswaId);

        $hasil = null;
        if ($quiz) {
            $hasil = HasilKuisSiswa::where('id_quiz', $quiz->id_quiz)
                ->where('id_siswa', $siswaId)
                ->latest('id_hasil')
                ->first();
        }

        if (!$hasil) {
            $hasil = HasilKuisSiswa::where('id_siswa', $siswaId)->latest('id_hasil')->first();
        }

        if (!$hasil) {
            return $this->error('Hasil ujian belum ditemukan untuk siswa ini', 404);
        }

        $isPublished = (bool) ($hasil->status_kirim ?? false);
        $waktu = $hasil->waktu_menit ?? 10;

        // Jika nilai belum dikirim / dirilis oleh guru ke seluruh siswa
        if (!$isPublished) {
            return $this->success([
                'id_quiz' => (int) ($quiz?->id_quiz ?? $hasil->id_quiz),
                'nama_siswa' => $siswa?->nm_siswa ?? 'Siswa',
                'is_published' => false,
                'status_publikasi' => 'Menunggu Pengumuman Nilai dari Guru',
                'pesan_banner' => 'Ujian Selesai Dikumpulkan!',
                'sub_pesan' => 'Jawabanmu telah tersimpan rapi di sistem. Nilai dan rincian benar/salah akan muncul secara otomatis segera setelah guru mengumumkan nilai ke seluruh siswa.',
                'durasi_menit' => $waktu,
                'nilai_akhir' => null,
                'jumlah_benar' => null,
                'jumlah_salah' => null,
                'is_lulus_kkm' => null,
                'label_kkm' => 'Menunggu Pengumuman',
                'status_teks' => 'Menunggu Nilai dari Guru',
                'mapel' => [
                    'nama' => $quiz?->judul_quiz ?? 'Ujian Online',
                    'guru' => $quiz?->guru?->nama_guru ?? 'Guru Pengampu',
                    'catatan_guru' => 'Nilai ujian sedang dalam proses verifikasi oleh bapak/ibu guru pengampu.',
                ],
            ], 'Ujian telah dikumpulkan, nilai menunggu pengumuman dari guru');
        }

        // Jika guru sudah merilis / mengirimkan nilai ke seluruh siswa
        $nilai = (int) $hasil->nilai_akhir;
        $benar = (int) $hasil->jumlah_benar;
        $salah = (int) $hasil->jumlah_salah;

        return $this->success([
            'id_quiz' => (int) ($quiz?->id_quiz ?? $hasil->id_quiz),
            'nama_siswa' => $siswa?->nm_siswa ?? 'Siswa',
            'is_published' => true,
            'status_publikasi' => 'Nilai Resmi Diumumkan',
            'pesan_banner' => 'Nilai Ujian Telah Diumumkan!',
            'sub_pesan' => 'Berikut adalah hasil skor resmi dari bapak/ibu guru pengampu.',
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
        ], 'Hasil ujian berhasil dimuat');
    }

    /**
     * Hitung progress kuis siswa per mata pelajaran berdasarkan bab yang terbuka (unlocked).
     * Aturan:
     * - Jika ada 1 bab terbuka & siswa sudah menyelesaikan 1 kuis di bab tersebut => 1/1 = 100% (1.0)
     * - Jika ada 2 bab terbuka & siswa baru menyelesaikan kuis di bab 1 => 1/2 = 50% (0.5)
     */
    private function calculateMapelProgress(int $idMapel, int $idSiswa): array
    {
        // 1. Ambil seluruh bab pada mapel ini (jika ada kolom is_locked, filter yang tidak terkunci)
        $unlockedBabsQuery = Bab::where('id_mapel', $idMapel);
        if (\Illuminate\Support\Facades\Schema::hasColumn('bab', 'is_locked')) {
            $unlockedBabsQuery->where('is_locked', false);
        }
        $unlockedBabs = $unlockedBabsQuery->get();

        $totalUnlocked = $unlockedBabs->count();
        if ($totalUnlocked === 0) {
            return [
                'progress' => 0.0,
                'persen' => 0,
                'bab_selesai' => 0,
                'bab_unlocked' => 0,
                'progress_text' => '0/0 Bab (0%)',
            ];
        }

        $completedBabCount = 0;
        foreach ($unlockedBabs as $bab) {
            $subBabIds = $bab->subBab()->pluck('id_sub_bab');
            if ($subBabIds->isEmpty()) {
                continue;
            }

            // Quiz bisa terhubung langsung ke sub-bab atau melalui materi kuis
            $materiIds = Materi::whereIn('id_sub_bab', $subBabIds)->pluck('id_materi');
            $quizIdsFromSubBab = Quiz::whereIn('id_sub_bab', $subBabIds)->pluck('id_quiz');
            $quizIdsFromMateriField = Quiz::whereIn('id_materi', $materiIds)->pluck('id_quiz');
            $quizIdsFromMateri = Materi::whereIn('id_sub_bab', $subBabIds)
                ->whereNotNull('id_quiz')
                ->pluck('id_quiz');

            $allQuizIds = $quizIdsFromSubBab
                ->merge($quizIdsFromMateriField)
                ->merge($quizIdsFromMateri)
                ->unique()
                ->filter()
                ->values();

            if ($allQuizIds->isNotEmpty()) {
                $hasCompleted = HasilKuisSiswa::whereIn('id_quiz', $allQuizIds)
                    ->where('id_siswa', $idSiswa)
                    ->exists();

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
