<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalMataPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RppController extends Controller
{
    /**
     * Tampilan RPP untuk Supervisor / Admin
     */
    public function index(Request $request)
    {
        // Jika login sebagai guru, arahkan ke tampilan RPP Guru
        if (session('user_type') === 'guru') {
            return $this->guruIndex($request);
        }

        $query = Rpp::with(['guru', 'mataPelajaran', 'kelas', 'jadwal']);

        $kpi = [
            'total' => Rpp::count(),
            'terverifikasi' => Rpp::where('status', 'terverifikasi')->count(),
            'perlu_review' => Rpp::whereIn('status', ['menunggu_review', 'draft', 'perlu_revisi'])->count(),
        ];

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('id_rooms')) {
            $query->where('id_rooms', $request->id_rooms);
        }
        if ($request->filled('id_mapel')) {
            $query->where('id_mapel', $request->id_mapel);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_rpp', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('guru', fn ($g) => $g->where('nama_guru', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%"));
            });
        }

        $rppList = $query->latest('created_at')->paginate(10)->withQueryString();
        $mapelList = MataPelajaran::all();
        $kelasList = Kelas::all();
        $namaGuru = Guru::all();

        return view('rpp.index', compact('rppList', 'kpi', 'mapelList', 'kelasList', 'namaGuru'));
    }

    /**
     * Tampilan RPP Khusus Portal Guru (Kurikulum Merdeka SD)
     */
    public function guruIndex(Request $request)
    {
        $guru = null;
        if (session()->has('user_id') && session('user_type') === 'guru') {
            $guru = Guru::find(session('user_id'));
        }
        if (! $guru) {
            $guru = Guru::first();
        }

        $baseQuery = Rpp::with(['guru', 'mataPelajaran', 'kelas', 'jadwal']);
        if ($guru) {
            $baseQuery->where('id_guru', $guru->id_guru);
        }

        // KPI Guru
        $totalRpp = (clone $baseQuery)->count();
        $siapAjar = (clone $baseQuery)->where('status', 'terverifikasi')->count();
        $menungguReview = (clone $baseQuery)->where('status', 'menunggu_review')->count();
        $perluRevisi = (clone $baseQuery)->where('status', 'perlu_revisi')->count();
        $draftCount = (clone $baseQuery)->where('status', 'draft')->count();

        $kpiGuru = [
            'total' => $totalRpp,
            'siap_ajar' => $siapAjar,
            'menunggu_review' => $menungguReview,
            'perlu_revisi' => $perluRevisi,
            'draft' => $draftCount,
            'perlu_dilengkapi' => $perluRevisi + $draftCount,
        ];

        // Query Filter List
        $query = clone $baseQuery;

        // Filter Tab
        $tab = $request->get('tab', 'semua');
        if ($tab === 'menunggu') {
            $query->where('status', 'menunggu_review');
        } elseif ($tab === 'disetujui' || $tab === 'arsip') {
            $query->where('status', 'terverifikasi');
        } elseif ($tab === 'revisi') {
            $query->where('status', 'perlu_revisi');
        } elseif ($tab === 'draft') {
            $query->where('status', 'draft');
        } elseif ($tab === 'jadwal') {
            $query->where(function ($q) {
                $q->whereNotNull('target_jadwal')->where('target_jadwal', '!=', '');
            })->where('status', 'terverifikasi');
        }

        // Filter Kelas
        if ($request->filled('id_rooms')) {
            $query->where('id_rooms', $request->id_rooms);
        }

        // Filter Mapel
        if ($request->filled('id_mapel')) {
            $query->where('id_mapel', $request->id_mapel);
        }

        // Filter Hari / Jadwal
        if ($request->filled('hari') && $request->hari !== 'semua') {
            $query->where('target_jadwal', 'like', "%{$request->hari}%");
        }

        // Filter Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_rpp', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('modul_ke', 'like', "%{$search}%")
                    ->orWhereHas('mataPelajaran', fn ($m) => $m->where('nama_mapel', 'like', "%{$search}%"));
            });
        }

        $rppList = $query->latest('id_rpp')->paginate(12)->withQueryString();
        // Revisi 2 & 3: di portal guru, dropdown modal RPP hanya menampilkan
        // kelas yang di-lock dan mapel yang diizinkan admin untuk guru ini.
        // Kalau guru belum punya mapel ampuan sama sekali, tampilkan semua
        // mapel supaya RPP pertama tetap bisa dibuat.
        $mapelList = MataPelajaran::all();
        $kelasList = Kelas::all();
        if ($guru) {
            if ($guru->lockedKelasId() !== null) {
                $kelasList = Kelas::where('id_rooms', $guru->lockedKelasId())->get();
            }
            $taughtIds = $guru->taughtMapelIds();
            if ($taughtIds->isNotEmpty()) {
                $mapelList = MataPelajaran::whereIn('id_mapel', $taughtIds)->get();
            }
        }
        $jadwalList = $guru ? JadwalMataPelajaran::with(['mataPelajaran', 'kelas'])->where('id_guru', $guru->id_guru)->get() : collect();

        return view('rpp_guru.index', compact(
            'rppList',
            'kpiGuru',
            'mapelList',
            'kelasList',
            'jadwalList',
            'guru',
            'tab'
        ));
    }

    /**
     * Validasi Target Roster Jadwal & Hitung JP Otomatis (1 JP = 45 Menit)
     *
     * @return array [bool $valid, ?string $errorMessage, ?string $alokasiJp]
     */
    private function processJadwalAndJp(?string $targetJadwal): array
    {
        if (empty($targetJadwal)) {
            return [true, null, null];
        }

        // Deteksi pola rentang jam, contoh: "09.00 - 08.00" atau "07:15 - 08:45"
        if (preg_match('/(\d{1,2})[:.](\d{2})\s*[-–]\s*(\d{1,2})[:.](\d{2})/', $targetJadwal, $m)) {
            $hMulai = (int) $m[1];
            $mMulai = (int) $m[2];
            $hSelesai = (int) $m[3];
            $mSelesai = (int) $m[4];

            if ($hMulai > 23 || $mMulai > 59 || $hSelesai > 23 || $mSelesai > 59) {
                return [false, 'Format jam atau menit pada Target Roster Jadwal tidak valid (jam: 00-23, menit: 00-59).', null];
            }

            $totalMulai = ($hMulai * 60) + $mMulai;
            $totalSelesai = ($hSelesai * 60) + $mSelesai;
            $durasi = $totalSelesai - $totalMulai;

            // 1. Cek jam pelajaran terbalik
            if ($durasi <= 0) {
                return [
                    false,
                    sprintf('Jam pelajaran terbalik! Jam mulai (%02d.%02d) tidak boleh lebih lambat atau sama dengan jam selesai (%02d.%02d).', $hMulai, $mMulai, $hSelesai, $mSelesai),
                    null
                ];
            }

            // 2. Cek durasi minimal (1 JP = 45 menit)
            if ($durasi < 45) {
                return [
                    false,
                    sprintf('Durasi pembelajaran pada jadwal (%d menit) terlalu singkat! Minimal 1 JP (45 menit).', $durasi),
                    null
                ];
            }

            // 3. Cek durasi maksimal (maksimal 4-5 JP / 225 menit untuk 1 modul ajar)
            if ($durasi > 225) {
                return [
                    false,
                    sprintf('Durasi pembelajaran pada jadwal (%d menit) terlalu panjang untuk satu modul ajar (maksimal 5 JP / 225 menit).', $durasi),
                    null
                ];
            }

            // 4. Durasi wajib kelipatan 1 JP (45 menit)
            if ($durasi % 45 !== 0) {
                return [
                    false,
                    sprintf('Durasi pembelajaran (%d menit) harus kelipatan 45 menit (1 JP), misalnya 45, 90, 135, 180, atau 225 menit.', $durasi),
                    null,
                ];
            }

            // 5. Hitung JP otomatis (1 JP = 45 menit)
            $jp = intdiv($durasi, 45);

            return [true, null, "{$jp} JP"];
        }

        return [true, null, null];
    }

    public function store(Request $request)
    {
        if (session('user_type') === 'admin') {
            return back()->with('error', 'Administrator tidak dapat menambahkan RPP langsung. RPP diajukan oleh guru untuk diverifikasi.');
        }

        $idGuru = $request->input('id_guru');
        if (! $idGuru && session('user_type') === 'guru') {
            $idGuru = session('user_id');
        }
        if (! $idGuru) {
            $idGuru = Guru::value('id_guru');
        }
        $request->merge(['id_guru' => $idGuru]);

        // Revisi 2 & 3: guru yang sudah di-lock admin ke 1 kelas tidak bisa
        // membuat RPP untuk kelas lain; kalau admin sudah mengatur mapelnya,
        // RPP hanya boleh untuk mapel yang diizinkan itu.
        $guruPembuat = $idGuru ? Guru::find($idGuru) : null;
        if ($guruPembuat && $guruPembuat->lockedKelasId() !== null) {
            $request->merge(['id_rooms' => $guruPembuat->lockedKelasId()]);
        }
        if ($guruPembuat && $guruPembuat->isMapelCustomized()
            && $request->filled('id_mapel')
            && ! $guruPembuat->canTeachMapel((int) $request->input('id_mapel'))) {
            return back()->withInput()->withErrors([
                'id_mapel' => 'Mata pelajaran ini bukan mata pelajaran yang diatur admin untuk Anda.',
            ]);
        }

        // Validasi Target Roster Jadwal & Hitung JP Otomatis (1 JP = 45 Menit)
        [$validJadwal, $errorJadwal, $autoJp] = $this->processJadwalAndJp($request->input('target_jadwal'));
        if (! $validJadwal) {
            return back()->withInput()->withErrors(['target_jadwal' => $errorJadwal]);
        }
        if ($autoJp) {
            $request->merge(['alokasi_waktu' => $autoJp]);
        }

        $validated = $request->validate([
            'id_guru' => 'required|exists:guru,id_guru',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
            'id_jadwal' => 'nullable|exists:jadwal_mata_pelajaran,id_jadwal',
            'judul_rpp' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\p{L}\p{N}\s.,\-:;()\"\'\/?!\r\n]+$/u',
            ],
            'alokasi_waktu' => ['nullable', 'string', 'max:15'],
            'fase' => ['nullable', 'string', 'max:20'],
            'modul_ke' => ['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\s]+$/u'],
            'target_jadwal' => ['nullable', 'string', 'max:50'],
            'ruang' => ['nullable', 'string', 'max:50'],
            'target_tanggal' => 'nullable|string|max:50',
            'custom_tags' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }
                    $tags = is_array($value) ? $value : array_filter(array_map('trim', explode(',', $value)));
                    foreach ($tags as $tag) {
                        if (! preg_match('/^[a-zA-Z0-9\s]+$/u', $tag)) {
                            $fail('Label / Tag hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).');

                            return;
                        }
                    }
                },
            ],
            'status' => 'nullable|in:draft,menunggu_review,terverifikasi,perlu_revisi',
            'file_rpp' => 'nullable|file|mimes:pdf,docx,doc|max:10240',
        ], [
            'judul_rpp.required' => 'Topik / Judul Modul Ajar wajib diisi.',
            'judul_rpp.min' => 'Topik / Judul Modul Ajar minimal 3 karakter.',
            'judul_rpp.max' => 'Topik / Judul Modul Ajar maksimal 100 karakter.',
            'judul_rpp.regex' => 'Topik / Judul Modul Ajar hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'modul_ke.max' => 'Nomor Modul atau Bab maksimal 20 karakter.',
            'modul_ke.regex' => 'Nomor Modul atau Bab hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'alokasi_waktu.max' => 'Alokasi Waktu maksimal 15 karakter (Contoh: 2 JP. Catatan: 1JP = 45 Menit).',
            'target_jadwal.max' => 'Target Roster Jadwal maksimal 50 karakter.',
            'ruang.max' => 'Ruang / Lokasi Belajar maksimal 50 karakter.',
            'deskripsi.max' => 'Capaian Pembelajaran / Deskripsi maksimal 500 karakter.',
            'deskripsi.regex' => 'Capaian Pembelajaran (TP) hanya boleh berisi huruf, angka, spasi, dan tanda baca kalimat (dilarang simbol aneh seperti #, $, %, ^, &, *).',
            'file_rpp.mimes' => 'Format berkas RPP & Modul hanya boleh berupa dokumen PDF (.pdf) atau Word (.docx, .doc).',
            'file_rpp.max' => 'Ukuran berkas RPP & Modul maksimal 10MB.',
        ]);

        if ($autoJp) {
            $validated['alokasi_waktu'] = $autoJp;
        }

        if ($request->hasFile('file_rpp')) {
            $validated['file_rpp'] = $request->file('file_rpp')->store('rpp_files', 'public');
        }

        $customTags = [];
        if ($request->filled('custom_tags')) {
            $customTags = is_array($request->custom_tags)
                ? $request->custom_tags
                : array_filter(array_map('trim', explode(',', $request->custom_tags)));
        }

        $validated['komponen_checklist'] = [
            'tujuan' => $request->boolean('tujuan'),
            'video' => $request->boolean('video'),
            'kktp' => $request->boolean('kktp'),
            'lkpd' => $request->boolean('lkpd'),
            'soal_proyektor' => $request->boolean('soal_proyektor'),
            'audio' => $request->boolean('audio'),
            'tags' => $customTags,
        ];

        if (session('user_type') === 'guru' || empty($validated['status'])) {
            $validated['status'] = $request->input('action') === 'draft' ? 'draft' : 'menunggu_review';
        }

        Rpp::create($validated);

        $redirectRoute = session('user_type') === 'guru' ? route('guru.rpp.index') : route('rpp.index');
        if ($validated['status'] === 'menunggu_review') {
            return redirect($redirectRoute)->with('success', 'Modul Ajar RPP berhasil dikirim. Status saat ini: "Menunggu Persetujuan Admin" dan belum bisa diaplikasikan hingga disetujui (di-accept) oleh Administrator.');
        }

        return redirect($redirectRoute)->with('success', 'Draf Modul Ajar RPP berhasil disimpan.');
    }

    public function show(Request $request, $id)
    {
        $rpp = Rpp::with(['guru', 'mataPelajaran', 'kelas', 'jadwal'])->where('id_rpp', $id)->first();
        if (! $rpp) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'RPP tidak ditemukan.'], 404);
            }
            abort(404, 'RPP tidak ditemukan.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($rpp);
        }

        return view('rpp.show', compact('rpp'));
    }

    public function update(Request $request, $id)
    {
        $rpp = Rpp::where('id_rpp', $id)->first();
        if (! $rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }

        // Revisi 2 & 3: paksa kelas & mapel sesuai lock admin (lihat store()).
        $guruPemilik = $rpp->id_guru ? Guru::find($rpp->id_guru) : null;
        if ($guruPemilik && $guruPemilik->lockedKelasId() !== null) {
            $request->merge(['id_rooms' => $guruPemilik->lockedKelasId()]);
        }
        if ($guruPemilik && $guruPemilik->isMapelCustomized()
            && $request->filled('id_mapel')
            && ! $guruPemilik->canTeachMapel((int) $request->input('id_mapel'))) {
            return back()->withInput()->withErrors([
                'id_mapel' => 'Mata pelajaran ini bukan mata pelajaran yang diatur admin untuk Anda.',
            ]);
        }

        // Validasi Target Roster Jadwal & Hitung JP Otomatis (1 JP = 45 Menit)
        [$validJadwal, $errorJadwal, $autoJp] = $this->processJadwalAndJp($request->input('target_jadwal'));
        if (! $validJadwal) {
            return back()->withInput()->withErrors(['target_jadwal' => $errorJadwal]);
        }
        if ($autoJp) {
            $request->merge(['alokasi_waktu' => $autoJp]);
        }

        $validated = $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
            'id_jadwal' => 'nullable|exists:jadwal_mata_pelajaran,id_jadwal',
            'judul_rpp' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\p{L}\p{N}\s.,\-:;()\"\'\/?!\r\n]+$/u',
            ],
            'alokasi_waktu' => ['nullable', 'string', 'max:15'],
            'fase' => ['nullable', 'string', 'max:20'],
            'modul_ke' => ['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\s]+$/u'],
            'target_jadwal' => ['nullable', 'string', 'max:50'],
            'ruang' => ['nullable', 'string', 'max:50'],
            'target_tanggal' => 'nullable|string|max:50',
            'custom_tags' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }
                    $tags = is_array($value) ? $value : array_filter(array_map('trim', explode(',', $value)));
                    foreach ($tags as $tag) {
                        if (! preg_match('/^[a-zA-Z0-9\s]+$/u', $tag)) {
                            $fail('Label / Tag hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).');

                            return;
                        }
                    }
                },
            ],
            'status' => 'nullable|in:draft,menunggu_review,terverifikasi,perlu_revisi',
            'file_rpp' => 'nullable|file|mimes:pdf,docx,doc|max:10240',
        ], [
            'judul_rpp.required' => 'Topik / Judul Modul Ajar wajib diisi.',
            'judul_rpp.min' => 'Topik / Judul Modul Ajar minimal 3 karakter.',
            'judul_rpp.max' => 'Topik / Judul Modul Ajar maksimal 100 karakter.',
            'judul_rpp.regex' => 'Topik / Judul Modul Ajar hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'modul_ke.max' => 'Nomor Modul atau Bab maksimal 20 karakter.',
            'modul_ke.regex' => 'Nomor Modul atau Bab hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'alokasi_waktu.max' => 'Alokasi Waktu maksimal 15 karakter (Contoh: 2 JP. Catatan: 1JP = 45 Menit).',
            'target_jadwal.max' => 'Target Roster Jadwal maksimal 50 karakter.',
            'ruang.max' => 'Ruang / Lokasi Belajar maksimal 50 karakter.',
            'deskripsi.max' => 'Capaian Pembelajaran / Deskripsi maksimal 500 karakter.',
            'deskripsi.regex' => 'Capaian Pembelajaran (TP) hanya boleh berisi huruf, angka, spasi, dan tanda baca kalimat (dilarang simbol aneh seperti #, $, %, ^, &, *).',
            'file_rpp.mimes' => 'Format berkas RPP & Modul hanya boleh berupa dokumen PDF (.pdf) atau Word (.docx, .doc).',
            'file_rpp.max' => 'Ukuran berkas RPP & Modul maksimal 10MB.',
        ]);

        if ($autoJp) {
            $validated['alokasi_waktu'] = $autoJp;
        }

        if ($request->hasFile('file_rpp')) {
            if ($rpp->file_rpp) {
                Storage::disk('public')->delete($rpp->file_rpp);
            }
            $validated['file_rpp'] = $request->file('file_rpp')->store('rpp_files', 'public');
        }

        $customTags = $rpp->komponen_checklist['tags'] ?? [];
        if ($request->has('custom_tags')) {
            $customTags = is_array($request->custom_tags)
                ? $request->custom_tags
                : array_filter(array_map('trim', explode(',', $request->custom_tags)));
        }

        $validated['komponen_checklist'] = [
            'tujuan' => $request->boolean('tujuan', $rpp->komponen_checklist['tujuan'] ?? false),
            'video' => $request->boolean('video', $rpp->komponen_checklist['video'] ?? false),
            'kktp' => $request->boolean('kktp', $rpp->komponen_checklist['kktp'] ?? false),
            'lkpd' => $request->boolean('lkpd', $rpp->komponen_checklist['lkpd'] ?? false),
            'soal_proyektor' => $request->boolean('soal_proyektor', $rpp->komponen_checklist['soal_proyektor'] ?? false),
            'audio' => $request->boolean('audio', $rpp->komponen_checklist['audio'] ?? false),
            'tags' => $customTags,
        ];

        if (session('user_type') === 'guru') {
            // Jika guru menyimpan sebagai draf, status draf. Jika diajukan, status menunggu_review agar admin menerima/menolak
            $validated['status'] = $request->input('action') === 'draft' ? 'draft' : 'menunggu_review';
        } elseif ($request->filled('status')) {
            $validated['status'] = $request->status;
        }

        $rpp->update($validated);

        $redirectRoute = session('user_type') === 'guru' ? route('guru.rpp.index') : route('rpp.index');
        if (session('user_type') === 'guru' && ($validated['status'] ?? '') === 'menunggu_review') {
            return redirect($redirectRoute)->with('success', 'Perubahan RPP berhasil dikirim. Status: "Menunggu Persetujuan Admin" dan belum bisa diaplikasikan hingga disetujui (di-accept) oleh Administrator.');
        }

        return redirect($redirectRoute)->with('success', 'RPP sukses diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,menunggu_review,terverifikasi,perlu_revisi',
            'catatan' => 'nullable|string|max:1000',
            'catatan_revisi' => 'nullable|string|max:1000',
        ]);

        $rpp = Rpp::where('id_rpp', $id)->first();
        if (! $rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }

        $statusLama = $rpp->status;
        $catatan = $request->input('catatan_revisi') ?? $request->input('catatan');

        $rpp->update([
            'status' => $request->status,
            'catatan_revisi' => $catatan,
        ]);

        $pesanSukses = match ($request->status) {
            'terverifikasi' => "Permintaan RPP '{$rpp->judul_rpp}' berhasil diterima dan disetujui.",
            'perlu_revisi' => "Permintaan RPP '{$rpp->judul_rpp}' dikembalikan ke guru untuk revisi.",
            default => 'Status RPP berhasil diperbarui.',
        };

        // Revisi 1: saat RPP baru saja diverifikasi, otomatis implementasikan
        // ke jadwal mengajar (buat jadwal baru atau hubungkan ke jadwal yang
        // sudah ada di slot yang sama). Verifikasi tidak gagal kalau jadwal
        // belum bisa dibuat — admin diberi peringatan, bukan error.
        if ($request->status === 'terverifikasi' && $statusLama !== 'terverifikasi') {
            $hasilJadwal = $this->autoImplementJadwal($rpp->fresh());
            if ($hasilJadwal['pesan']) {
                $pesanSukses .= ' '.$hasilJadwal['pesan'];
            }
        }

        return back()->with('success', $pesanSukses);
    }

    /**
     * Revisi 1 — RPP auto-implement ke jadwal.
     * Parse target_jadwal ("Senin, 08.00 - 09.30 WIB") jadi hari + jam
     * "08.00-09.30", lalu cari/buat JadwalMataPelajaran untuk
     * guru + mapel + kelas RPP tersebut.
     *
     * @return array{jadwal: ?JadwalMataPelajaran, pesan: ?string}
     */
    private function autoImplementJadwal(Rpp $rpp): array
    {
        if ($rpp->id_jadwal) {
            return ['jadwal' => $rpp->jadwal, 'pesan' => 'RPP sudah terhubung ke jadwal yang ada.'];
        }
        if (! $rpp->id_guru || ! $rpp->id_mapel || ! $rpp->id_rooms) {
            return ['jadwal' => null, 'pesan' => 'Jadwal belum dibuat otomatis: RPP ini belum punya guru, mapel, atau kelas yang lengkap.'];
        }

        $parsed = $this->parseTargetJadwal($rpp->target_jadwal);
        if (! $parsed) {
            return ['jadwal' => null, 'pesan' => 'Jadwal belum dibuat otomatis: target jadwal RPP belum berisi hari dan rentang jam yang bisa dibaca (contoh: Senin, 08.00 - 09.30).'];
        }
        [$hari, $jam] = $parsed;

        // Sudah ada jadwal persis di slot itu? Hubungkan saja, jangan duplikasi.
        $sudahAda = JadwalMataPelajaran::where('hari', $hari)
            ->where('jam', $jam)
            ->where('id_guru', $rpp->id_guru)
            ->where('id_mapel', $rpp->id_mapel)
            ->where('id_rooms', $rpp->id_rooms)
            ->first();
        if ($sudahAda) {
            $rpp->update(['id_jadwal' => $sudahAda->id_jadwal]);

            return ['jadwal' => $sudahAda, 'pesan' => "RPP terhubung ke jadwal {$hari} jam {$jam} yang sudah ada."];
        }

        // Cek bentrok (guru atau kelas sudah terisi di jam yang beririsan).
        if ($this->jadwalBentrok($hari, $jam, $rpp->id_guru, $rpp->id_rooms)) {
            return ['jadwal' => null, 'pesan' => "Jadwal belum dibuat otomatis: {$hari} jam {$jam} bentrok dengan jadwal guru/kelas lain. Buat jadwal manual dari menu Jadwal."];
        }

        $jadwal = JadwalMataPelajaran::create([
            'hari' => $hari,
            'jam' => $jam,
            'id_mapel' => $rpp->id_mapel,
            'id_guru' => $rpp->id_guru,
            'id_rooms' => $rpp->id_rooms,
        ]);
        $rpp->update(['id_jadwal' => $jadwal->id_jadwal]);

        return ['jadwal' => $jadwal, 'pesan' => "Jadwal otomatis dibuat: {$hari} jam {$jam}."];
    }

    /** "Senin, 08.00 - 09.30 WIB" -> ['Senin', '08.00-09.30'] atau null. */
    private function parseTargetJadwal(?string $targetJadwal): ?array
    {
        if (! $targetJadwal) {
            return null;
        }
        if (! preg_match('/(Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu)/i', $targetJadwal, $hariMatch)) {
            return null;
        }
        if (! preg_match('/(\d{1,2})[:.](\d{2})\s*[-–]\s*(\d{1,2})[:.](\d{2})/', $targetJadwal, $m)) {
            return null;
        }
        $hari = ucfirst(strtolower($hariMatch[1]));
        $mulai = sprintf('%02d.%02d', (int) $m[1], (int) $m[2]);
        $selesai = sprintf('%02d.%02d', (int) $m[3], (int) $m[4]);
        if (($m[3] * 60 + $m[4]) <= ($m[1] * 60 + $m[2])) {
            return null;
        }

        return [$hari, "{$mulai}-{$selesai}"];
    }

    /** true kalau ada jadwal lain yang beririsan jamnya untuk guru/kelas ini. */
    private function jadwalBentrok(string $hari, string $jam, int $idGuru, int $idRooms): bool
    {
        $rentangBaru = $this->parseRentangJam($jam);
        if (! $rentangBaru) {
            return false;
        }
        [$mulaiBaru, $selesaiBaru] = $rentangBaru;

        $jadwalLain = JadwalMataPelajaran::where('hari', $hari)
            ->where(function ($q) use ($idGuru, $idRooms) {
                $q->where('id_guru', $idGuru)->orWhere('id_rooms', $idRooms);
            })->get();

        foreach ($jadwalLain as $j) {
            $rentangLama = $this->parseRentangJam($j->jam);
            if (! $rentangLama) {
                continue;
            }
            [$mulaiLama, $selesaiLama] = $rentangLama;
            if ($mulaiBaru < $selesaiLama && $mulaiLama < $selesaiBaru) {
                return true;
            }
        }

        return false;
    }

    /** "08.00-09.30" -> [480, 570] (menit) atau null. */
    private function parseRentangJam(string $jam): ?array
    {
        $bersih = preg_replace('/\s+/', '', str_replace(':', '.', $jam));
        $parts = explode('-', (string) $bersih);
        if (count($parts) !== 2) {
            return null;
        }
        $mulai = explode('.', $parts[0]);
        $selesai = explode('.', $parts[1]);
        if (count($mulai) !== 2 || count($selesai) !== 2) {
            return null;
        }

        return [(int) $mulai[0] * 60 + (int) $mulai[1], (int) $selesai[0] * 60 + (int) $selesai[1]];
    }

    public function destroy($id)
    {
        $rpp = Rpp::where('id_rpp', $id)->first();
        if (! $rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }
        if ($rpp->file_rpp) {
            Storage::disk('public')->delete($rpp->file_rpp);
        }
        $rpp->delete();

        return back()->with('success', 'RPP sukses dihapus.');
    }

    public function download($id)
    {
        $rpp = Rpp::with(['guru', 'mataPelajaran', 'kelas'])->where('id_rpp', $id)->first();
        if (! $rpp) {
            abort(404, 'Berkas RPP tidak ditemukan.');
        }

        if ($rpp->file_rpp && Storage::disk('public')->exists($rpp->file_rpp)) {
            return response()->download(storage_path('app/public/'.$rpp->file_rpp), basename($rpp->file_rpp));
        }

        // Jika belum ada berkas upload fisik, buka dokumen resmi Modul Ajar siap cetak & simpan PDF
        return view('rpp_guru.cetak', compact('rpp'));
    }
}
