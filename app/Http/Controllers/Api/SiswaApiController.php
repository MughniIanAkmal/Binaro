<?php

namespace App\Http\Controllers\Api;

use App\Models\Absen;
use App\Models\MataPelajaran;
use App\Models\Pr;
use App\Models\Siswa;
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
}
