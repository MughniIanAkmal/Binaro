<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Notifikasi;
use App\Models\Pr;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotifikasiPrController extends Controller
{
    /**
     * Tampilkan halaman utama Kelola Notifikasi & PR (Dashboard CRUD).
     */
    public function index(Request $request)
    {
        $guruId = session('user_id');
        $guru = $guruId ? Guru::find($guruId) : Guru::first();
        if (!$guru) {
            $guru = Guru::first();
        }

        $search = $request->query('search');
        $mapelFilter = $request->query('mapel_id');
        $activeTab = $request->query('tab', 'pr');

        // 1. Query PR
        $prQuery = Pr::with(['mataPelajaran', 'guru'])
            ->withCount('notifikasi');

        if ($search) {
            $prQuery->where(function ($q) use ($search) {
                $q->where('nama_pr', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('mataPelajaran', fn($m) => $m->where('nama_mapel', 'like', "%{$search}%"));
            });
        }

        if ($mapelFilter) {
            $prQuery->where('id_mapel', $mapelFilter);
        }

        $prs = $prQuery->latest('id_pr')->paginate(8, ['*'], 'pr_page')->withQueryString();

        // 2. Query Log Notifikasi
        $notifikasiQuery = Notifikasi::with(['guru', 'siswa.kelas', 'pr.mataPelajaran']);

        if ($search) {
            $notifikasiQuery->where(function ($q) use ($search) {
                $q->where('pesan', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn($s) => $s->where('nm_siswa', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%"))
                  ->orWhereHas('pr', fn($p) => $p->where('nama_pr', 'like', "%{$search}%"));
            });
        }

        $notifikasis = $notifikasiQuery->latest('id_notifikasi')->paginate(12, ['*'], 'notif_page')->withQueryString();

        // 3. KPI Statistics
        $totalPr = Pr::count();
        $prAktif = Pr::where('tgl_tenggat', '>=', Carbon::now())->count();
        $totalNotifikasi = Notifikasi::count();
        $totalSiswa = Siswa::count();
        $siswaTerjangkau = Notifikasi::distinct('id_siswa')->count('id_siswa');
        $notifikasiDibaca = Notifikasi::where('status_baca', 1)->count();
        $readRate = $totalNotifikasi > 0 ? round(($notifikasiDibaca / $totalNotifikasi) * 100) : 0;

        // Data Master untuk Form & Modal
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        $kelas = Kelas::withCount('siswas')->get();
        $siswas = Siswa::with('kelas')->orderBy('nm_siswa')->get();
        $allPrs = Pr::with('mataPelajaran')->latest('id_pr')->get();

        return view('guru.notifikasi_pr.index', compact(
            'guru',
            'prs',
            'notifikasis',
            'totalPr',
            'prAktif',
            'totalNotifikasi',
            'totalSiswa',
            'siswaTerjangkau',
            'readRate',
            'mapels',
            'kelas',
            'siswas',
            'allPrs',
            'search',
            'mapelFilter',
            'activeTab'
        ));
    }

    /**
     * Form Tambah Tugas PR Baru & Kirim Notifikasi.
     */
    public function create()
    {
        $guruId = session('user_id');
        $guru = $guruId ? Guru::find($guruId) : Guru::first();
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        $kelas = Kelas::withCount('siswas')->get();
        $siswas = Siswa::with('kelas')->orderBy('nm_siswa')->get();

        // Hitung default tanggal tenggat (besok atau hari sekolah terdekat, lewati Minggu)
        $defaultDate = Carbon::now()->addDay();
        while ($defaultDate->isSunday() || ($defaultDate->isToday() && Carbon::now()->hour >= 14)) {
            $defaultDate->addDay();
        }
        $defaultTanggal = $defaultDate->format('Y-m-d');

        return view('guru.notifikasi_pr.create', compact('guru', 'mapels', 'kelas', 'siswas', 'defaultTanggal'));
    }

    /**
     * Validasi Human Error Batas Waktu Tenggat PR:
     * 1. Tidak boleh di masa lalu (harus lebih besar dari waktu realtime saat ini).
     * 2. Tidak boleh hari Minggu (hari libur sekolah).
     * 3. Harus di dalam jam operasional sekolah (07:00 - 14:00 WIB).
     */
    private function validateTenggatWaktu($tglTenggat)
    {
        $dt = Carbon::parse($tglTenggat);
        $now = Carbon::now();

        // 1. Lewat hari dan jam realtime
        if ($dt->lessThanOrEqualTo($now)) {
            return "Batas waktu pengumpulan PR (" . $dt->format('d/m/Y H:i') . " WIB) tidak diperbolehkan karena sudah lewat dari waktu realtime sekarang. Silakan tentukan waktu tenggat di masa depan.";
        }

        // 2. Hari Minggu libur (Sunday = 0)
        if ($dt->dayOfWeek === Carbon::SUNDAY) {
            return "Batas pengumpulan PR tidak boleh jatuh pada hari Minggu (" . $dt->translatedFormat('d F Y') . ") karena hari libur sekolah. Silakan pilih hari operasional sekolah (Senin s/d Sabtu).";
        }

        // 3. Jam operasional sekolah 07:00 - 14:00 WIB
        $timeNumber = (int)$dt->format('Hi');
        if ($timeNumber < 700 || $timeNumber > 1400) {
            return "Jam batas pengumpulan PR (" . $dt->format('H:i') . " WIB) berada di luar jam operasional sekolah. Jam operasional sekolah SDN Kalitapen 01 adalah pukul 07:00 sampai 14:00 WIB.";
        }

        return null;
    }

    /**
     * Simpan PR baru dan langsung kirim notifikasi ke siswa jika dipilih.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pr' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/u',
            ],
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'tgl_tenggat' => 'required|date',
            'deskripsi' => 'nullable|string|max:1000',
            'kirim_notifikasi' => 'nullable|boolean',
            'target_tipe' => 'nullable|in:semua,kelas,siswa',
            'target_kelas' => 'nullable|exists:kelas,id_rooms',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'exists:siswa,id_siswa',
            'pesan_kustom' => 'nullable|string|max:1000',
        ], [
            'nama_pr.required' => 'Nama tugas PR wajib diisi.',
            'nama_pr.min'      => 'Nama tugas PR minimal 3 karakter.',
            'nama_pr.max'      => 'Nama tugas PR maksimal 100 karakter.',
            'nama_pr.regex'    => 'Nama tugas PR hanya boleh berisi huruf, angka, spasi, dan tanda baca standar (- . , _ ()).',
            'deskripsi.max'    => 'Deskripsi petunjuk PR maksimal 1000 karakter.',
        ]);

        // Human Error Guardrail: Validasi Tenggat Waktu
        $errorTenggat = $this->validateTenggatWaktu($request->tgl_tenggat);
        if ($errorTenggat) {
            return back()->withInput()->with('error', $errorTenggat);
        }

        // Human Error Guardrail: Cegah PR yang sama persis pada mata pelajaran yang sama jika masih aktif
        $isPrDuplicate = Pr::where('id_mapel', $request->id_mapel)
            ->where('nama_pr', trim($request->nama_pr))
            ->where('tgl_tenggat', '>=', Carbon::now())
            ->exists();

        if ($isPrDuplicate) {
            return back()->withInput()->with('error', "Tugas PR dengan nama '" . trim($request->nama_pr) . "' pada mata pelajaran ini sudah ada dan tenggatnya masih aktif. Gunakan nama PR yang berbeda untuk menghindari duplikasi tugas.");
        }

        try {
            $guruId = session('user_id');
            if (!$guruId) {
                $firstGuru = Guru::first();
                $guruId = $firstGuru ? $firstGuru->id_guru : 1;
            }

            $pr = Pr::create([
                'id_mapel' => $request->id_mapel,
                'id_guru' => $guruId,
                'nama_pr' => trim($request->nama_pr),
                'deskripsi' => $request->deskripsi,
                'tgl_tenggat' => Carbon::parse($request->tgl_tenggat)->format('Y-m-d H:i:s'),
            ]);

            $countSent = 0;
            $countDuplicates = 0;

            // Cek apakah opsi kirim notifikasi dicentang
            if ($request->boolean('kirim_notifikasi')) {
                $siswaIds = [];

                if ($request->target_tipe === 'kelas' && $request->filled('target_kelas')) {
                    $siswaIds = Siswa::where('id_rooms', $request->target_kelas)->pluck('id_siswa')->toArray();
                } elseif ($request->target_tipe === 'siswa' && !empty($request->target_siswa)) {
                    $siswaIds = $request->target_siswa;
                } else {
                    // Default: Semua siswa
                    $siswaIds = Siswa::pluck('id_siswa')->toArray();
                }

                if (!empty($siswaIds)) {
                    $mapel = MataPelajaran::find($request->id_mapel);
                    $mapelNama = $mapel ? $mapel->nama_mapel : 'Mata Pelajaran';
                    $tenggatCarbon = Carbon::parse($pr->tgl_tenggat);
                    $tenggatFormat = $tenggatCarbon->translatedFormat('d M Y') . ', ' . $tenggatCarbon->format('H:i') . ' WIB';

                    $pesanDefault = "Tugas PR Baru: '{$pr->nama_pr}' ({$mapelNama}). Batas pengumpulan: {$tenggatFormat}. Silakan periksa instruksi tugas dan kumpulkan tepat waktu.";
                    $pesanFinal = $request->filled('pesan_kustom') ? trim($request->pesan_kustom) : $pesanDefault;

                    // Human Error Guardrail: Cegah notifikasi yang sama persis
                    // Karena PR baru dibuat, cek apakah pesan yang akan dikirim
                    // sudah pernah dikirim sebelumnya untuk PR lain dengan pesan sama ke siswa yang sama
                    $notifSudahAda = Notifikasi::where('pesan', $pesanFinal)
                        ->whereIn('id_siswa', $siswaIds)
                        ->exists();

                    if ($notifSudahAda) {
                        // Hapus PR yang baru saja dibuat karena notifikasi tidak bisa dikirim
                        $pr->delete();
                        return back()->withInput()
                            ->with('error', "Gagal menyimpan: Notifikasi dengan isi pesan yang sama persis sudah pernah dikirimkan kepada siswa yang dituju sebelumnya. Ubah isi pesan notifikasi agar berbeda dari yang sudah ada, atau matikan opsi kirim notifikasi.");
                    }

                    foreach ($siswaIds as $idSiswa) {
                        $alreadySent = Notifikasi::where('id_siswa', $idSiswa)
                            ->where('id_pr', $pr->id_pr)
                            ->where('pesan', $pesanFinal)
                            ->exists();

                        if ($alreadySent) {
                            $countDuplicates++;
                            continue;
                        }

                        Notifikasi::create([
                            'id_guru' => $guruId,
                            'id_siswa' => $idSiswa,
                            'id_pr' => $pr->id_pr,
                            'pesan' => $pesanFinal,
                            'status_baca' => 0,
                        ]);
                        $countSent++;
                    }
                }
            }

            $pesanSukses = "Tugas PR '{$pr->nama_pr}' berhasil ditambahkan.";
            if ($countSent > 0) {
                $pesanSukses .= " Notifikasi berhasil dikirimkan kepada {$countSent} siswa.";
                if ($countDuplicates > 0) {
                    $pesanSukses .= " ({$countDuplicates} siswa dilewati karena sudah menerima notifikasi yang sama).";
                }
            }

            return redirect()->route('guru.notifikasi_pr.index')->with('success', $pesanSukses);

        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error constraint database (misalnya duplikasi unique key)
            $errorCode = $e->errorInfo[1] ?? 0;
            if ($errorCode == 1062) {
                return back()->withInput()->with('error', 'Gagal menyimpan: Data tugas PR atau notifikasi serupa sudah ada di dalam sistem (duplikasi terdeteksi oleh database). Periksa kembali nama PR dan penerima notifikasi.');
            }
            return back()->withInput()->with('error', 'Gagal menyimpan tugas PR karena terjadi kesalahan pada database: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return back()->withInput()->with('error', 'Terjadi kesalahan tidak terduga saat menyimpan tugas PR. Silakan coba kembali. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail PR beserta daftar siswa yang sudah menerima notifikasi.
     */
    public function show($id)
    {
        $pr = Pr::with(['mataPelajaran', 'guru', 'notifikasi.siswa.kelas'])->findOrFail($id);
        $guruId = session('user_id');
        $guru = $guruId ? Guru::find($guruId) : Guru::first();
        $kelas = Kelas::withCount('siswas')->get();
        $siswas = Siswa::with('kelas')->orderBy('nm_siswa')->get();

        return view('guru.notifikasi_pr.show', compact('pr', 'guru', 'kelas', 'siswas'));
    }

    /**
     * Form Edit Tugas PR.
     */
    public function edit($id)
    {
        $pr = Pr::with('mataPelajaran')->findOrFail($id);
        $guruId = session('user_id');
        $guru = $guruId ? Guru::find($guruId) : Guru::first();
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();

        return view('guru.notifikasi_pr.edit', compact('pr', 'guru', 'mapels'));
    }

    /**
     * Simpan perubahan PR dan opsi kirim notifikasi pembaruan.
     */
    public function update(Request $request, $id)
    {
        $pr = Pr::findOrFail($id);

        $request->validate([
            'nama_pr' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_.,()]+$/u',
            ],
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'tgl_tenggat' => 'required|date',
            'deskripsi' => 'nullable|string|max:1000',
            'kirim_notifikasi_update' => 'nullable|boolean',
            'pesan_update' => 'nullable|string|max:1000',
        ], [
            'nama_pr.required' => 'Nama tugas PR wajib diisi.',
            'nama_pr.min'      => 'Nama tugas PR minimal 3 karakter.',
            'nama_pr.max'      => 'Nama tugas PR maksimal 100 karakter.',
            'nama_pr.regex'    => 'Nama tugas PR hanya boleh berisi huruf, angka, spasi, dan tanda baca standar (- . , _ ()).',
            'deskripsi.max'    => 'Deskripsi petunjuk PR maksimal 1000 karakter.',
        ]);

        // Human Error Guardrail: Validasi Tenggat Waktu
        $errorTenggat = $this->validateTenggatWaktu($request->tgl_tenggat);
        if ($errorTenggat) {
            return back()->withInput()->with('error', $errorTenggat);
        }

        try {
            $pr->update([
                'nama_pr' => trim($request->nama_pr),
                'id_mapel' => $request->id_mapel,
                'tgl_tenggat' => Carbon::parse($request->tgl_tenggat)->format('Y-m-d H:i:s'),
                'deskripsi' => $request->deskripsi,
            ]);

            $countSent = 0;
            $countDuplicates = 0;

            if ($request->boolean('kirim_notifikasi_update')) {
                $guruId = session('user_id') ?? $pr->id_guru;
                $recipientIds = Notifikasi::where('id_pr', $pr->id_pr)->pluck('id_siswa')->unique()->toArray();

                // Jika belum ada riwayat notifikasi sebelumnya, kirim ke semua siswa
                if (empty($recipientIds)) {
                    $recipientIds = Siswa::pluck('id_siswa')->toArray();
                }

                $mapel = MataPelajaran::find($pr->id_mapel);
                $tenggatCarbon = Carbon::parse($pr->tgl_tenggat);
                $tenggatFormat = $tenggatCarbon->translatedFormat('d M Y') . ', ' . $tenggatCarbon->format('H:i') . ' WIB';
                $pesanDefault = "Pembaruan Tugas: PR '{$pr->nama_pr}' ({$mapel?->nama_mapel}) diperbarui. Tenggat pengumpulan: {$tenggatFormat}. Silakan cek catatan tugas terbaru.";
                $pesanFinal = $request->filled('pesan_update') ? trim($request->pesan_update) : $pesanDefault;

                // Human Error Guardrail: Cek batch duplikasi notifikasi - apakah pesan ini sudah pernah dikirim ke semua penerima?
                $sudahDuplikatSemua = Notifikasi::where('id_pr', $pr->id_pr)
                    ->where('pesan', $pesanFinal)
                    ->whereIn('id_siswa', $recipientIds)
                    ->count() >= count($recipientIds);

                if ($sudahDuplikatSemua) {
                    return redirect()->route('guru.notifikasi_pr.index')
                        ->with('error', "PR '{$pr->nama_pr}' berhasil diperbarui, namun notifikasi revisi tidak dikirim karena notifikasi dengan isi pesan yang sama sudah pernah dikirimkan kepada seluruh penerima yang dituju.");
                }

                foreach ($recipientIds as $idSiswa) {
                    // Human Error Guardrail: Cegah notifikasi yang sama persis per siswa
                    $alreadySent = Notifikasi::where('id_siswa', $idSiswa)
                        ->where('id_pr', $pr->id_pr)
                        ->where('pesan', $pesanFinal)
                        ->exists();

                    if ($alreadySent) {
                        $countDuplicates++;
                        continue;
                    }

                    Notifikasi::create([
                        'id_guru' => $guruId,
                        'id_siswa' => $idSiswa,
                        'id_pr' => $pr->id_pr,
                        'pesan' => $pesanFinal,
                        'status_baca' => 0,
                    ]);
                    $countSent++;
                }
            }

            $pesanSukses = "Tugas PR '{$pr->nama_pr}' berhasil diperbarui.";
            if ($countSent > 0) {
                $pesanSukses .= " Notifikasi pembaruan terkirim ke {$countSent} siswa.";
                if ($countDuplicates > 0) {
                    $pesanSukses .= " ({$countDuplicates} siswa dilewati karena sudah menerima notifikasi yang sama).";
                }
            }

            return redirect()->route('guru.notifikasi_pr.index')->with('success', $pesanSukses);

        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error constraint database (misalnya duplikasi unique key)
            $errorCode = $e->errorInfo[1] ?? 0;
            if ($errorCode == 1062) {
                return back()->withInput()->with('error', 'Gagal menyimpan perubahan: Notifikasi serupa sudah ada di dalam sistem (duplikasi terdeteksi oleh database). Ubah isi pesan atau pilih penerima yang berbeda.');
            }
            return back()->withInput()->with('error', 'Gagal menyimpan perubahan PR karena terjadi kesalahan pada database: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return back()->withInput()->with('error', 'Terjadi kesalahan tidak terduga saat menyimpan perubahan PR. Silakan coba kembali. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Tugas PR beserta seluruh notifikasi yang terkait.
     */
    public function destroy($id)
    {
        $pr = Pr::findOrFail($id);
        $namaPr = $pr->nama_pr;

        // Hapus semua notifikasi terkait PR ini
        Notifikasi::where('id_pr', $pr->id_pr)->delete();

        $pr->delete();

        return redirect()->route('guru.notifikasi_pr.index')->with('success', "Tugas PR '{$namaPr}' dan seluruh riwayat notifikasinya berhasil dihapus.");
    }

    /**
     * Endpoint khusus untuk mengirim / menyiarkan (broadcast) notifikasi PR ke siswa kapan saja.
     */
    public function kirim(Request $request)
    {
        $request->validate([
            'id_pr' => 'required|exists:pr,id_pr',
            'target_tipe' => 'required|in:semua,kelas,siswa',
            'target_kelas' => 'nullable|exists:kelas,id_rooms',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'exists:siswa,id_siswa',
            'pesan' => 'required|string|max:1000',
        ]);

        $guruId = session('user_id');
        if (!$guruId) {
            $firstGuru = Guru::first();
            $guruId = $firstGuru ? $firstGuru->id_guru : 1;
        }

        $pr = Pr::with('mataPelajaran')->findOrFail($request->id_pr);

        $siswaIds = [];
        if ($request->target_tipe === 'kelas' && $request->filled('target_kelas')) {
            $siswaIds = Siswa::where('id_rooms', $request->target_kelas)->pluck('id_siswa')->toArray();
        } elseif ($request->target_tipe === 'siswa' && !empty($request->target_siswa)) {
            $siswaIds = $request->target_siswa;
        } else {
            // Semua Siswa
            $siswaIds = Siswa::pluck('id_siswa')->toArray();
        }

        if (empty($siswaIds)) {
            return back()->with('error', 'Tidak ada siswa yang dipilih atau ditemukan pada target tersebut.');
        }

        $pesanTrimmed = trim($request->pesan);
        $countSent = 0;
        $countDuplicates = 0;

        foreach ($siswaIds as $idSiswa) {
            // Human Error Guardrail: Cegah notifikasi yang sama persis
            $alreadySent = Notifikasi::where('id_siswa', $idSiswa)
                ->where('id_pr', $pr->id_pr)
                ->where('pesan', $pesanTrimmed)
                ->exists();

            if ($alreadySent) {
                $countDuplicates++;
                continue;
            }

            Notifikasi::create([
                'id_guru' => $guruId,
                'id_siswa' => $idSiswa,
                'id_pr' => $pr->id_pr,
                'pesan' => $pesanTrimmed,
                'status_baca' => 0,
            ]);
            $countSent++;
        }

        if ($countSent === 0 && $countDuplicates > 0) {
            return back()->with('error', 'Gagal mengirim: Notifikasi dengan isi pesan yang sama persis sudah pernah dikirimkan sebelumnya kepada seluruh siswa yang dipilih.');
        }

        $msg = "Notifikasi PR '{$pr->nama_pr}' berhasil dikirimkan ke {$countSent} siswa.";
        if ($countDuplicates > 0) {
            $msg .= " ({$countDuplicates} siswa dilewati karena sudah menerima notifikasi yang sama).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Hapus satu item notifikasi dari riwayat.
     */
    public function destroyNotifikasi($id)
    {
        $notif = Notifikasi::findOrFail($id);
        $notif->delete();

        return back()->with('success', 'Riwayat notifikasi berhasil dihapus.');
    }
}
