<?php

namespace App\Features\Controllers;

use App\Features\Models\Bab;
use App\Features\Models\CapaianPembelajaran;
use App\Features\Models\Guru;
use App\Features\Models\JadwalMataPelajaran;
use App\Features\Models\Kelas;
use App\Features\Models\MataPelajaran;
use App\Features\Models\Rpp;
use App\Features\Models\RppCatatan;
use App\Features\Models\RppSession;
use App\Features\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

        $query = Rpp::with(['guru', 'mataPelajaran', 'kelas', 'jadwal', 'bab', 'catatan']);

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
        if ($request->filled('id_bab')) {
            $query->where('id_bab', $request->id_bab);
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
        $babList = Bab::orderBy('id_mapel')->orderBy('id_bab')->get();

        return view('rpp.index', compact('rppList', 'kpi', 'mapelList', 'kelasList', 'namaGuru', 'babList'));
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

        $baseQuery = Rpp::with(['guru', 'mataPelajaran', 'kelas', 'jadwal', 'bab']);
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

        // Peta cakupan bab: guru perlu tahu bab mana yang sudah punya RPP dan
        // mana yang belum, supaya tidak membuat dokumen ganda untuk bab sama.
        $mapelIds = $mapelList->pluck('id_mapel')->all();
        $babList = Bab::whereIn('id_mapel', $mapelIds)->orderBy('id_mapel')->orderBy('id_bab')->get();
        $rppPerBab = $guru
            ? Rpp::where('id_guru', $guru->id_guru)->whereNotNull('id_bab')
                ->get(['id_rpp', 'id_bab', 'status', 'judul_rpp'])
                ->keyBy('id_bab')
            : collect();

        return view('rpp_guru.index', compact(
            'rppList',
            'kpiGuru',
            'mapelList',
            'kelasList',
            'jadwalList',
            'guru',
            'tab',
            'babList',
            'rppPerBab'
        ));
    }

    public function wizardCreate(Request $request)
    {
        $guru = Guru::findOrFail(session('user_id'));
        $mapelList = MataPelajaran::all();
        $kelasList = Kelas::all();
        if ($guru->lockedKelasId() !== null) {
            $kelasList = Kelas::where('id_rooms', $guru->lockedKelasId())->get();
        }
        $taughtIds = $guru->taughtMapelIds();
        if ($taughtIds->isNotEmpty()) {
            $mapelList = MataPelajaran::whereIn('id_mapel', $taughtIds)->get();
        }
        $babList = Bab::whereIn('id_mapel', $mapelList->pluck('id_mapel'))->with('mataPelajaran')->orderBy('id_mapel')->orderBy('id_bab')->get();

        return view('rpp_guru.wizard', [
            'rpp' => new Rpp(['total_pertemuan' => 1]),
            'guru' => $guru,
            'mapelList' => $mapelList,
            'kelasList' => $kelasList,
            'cpList' => collect(),
            'step' => 1,
            'nomorPertemuan' => 1,
            'rppSession' => new RppSession(['konten' => []]),
            'isNew' => true,
        ]);
    }

    public function wizardStore(Request $request)
    {
        abort_unless(session('user_type') === 'guru', 403);
        $guru = Guru::findOrFail(session('user_id'));
        $validated = $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'required|exists:kelas,id_rooms',
            'id_bab' => 'nullable|exists:bab,id_bab',
            'judul_rpp' => 'required|string|min:3|max:150',
            'semester' => 'required|in:ganjil,genap',
            'tahun_ajaran' => 'required|string|max:20|regex:/^\\d{4}\\/\\d{4}$/',
            'alokasi_jp' => 'required|integer|min:1|max:20',
            'total_pertemuan' => 'required|integer|min:1|max:20',
            'target_jadwal' => 'nullable|string|max:50',
            'ruang' => 'nullable|string|max:50',
            'target_tanggal' => 'nullable|string|max:50',
        ]);

        if ($guru->lockedKelasId() !== null && (int) $validated['id_rooms'] !== (int) $guru->lockedKelasId()) {
            throw ValidationException::withMessages(['id_rooms' => 'Kelas ini bukan kelas yang diatur untuk Anda.']);
        }
        if ($guru->isMapelCustomized() && ! $guru->canTeachMapel((int) $validated['id_mapel'])) {
            throw ValidationException::withMessages(['id_mapel' => 'Mata pelajaran ini bukan mata pelajaran yang diatur admin untuk Anda.']);
        }
        $this->pastikanBabSesuaiMapel($validated['id_bab'] ?? null, (int) $validated['id_mapel']);

        $fase = $this->faseRppUntukKelas(Kelas::findOrFail($validated['id_rooms']));
        if (! $fase) {
            throw ValidationException::withMessages(['id_rooms' => 'Tingkat kelas belum tersedia untuk menentukan fase pembelajaran.']);
        }

        $rpp = DB::transaction(function () use ($validated, $guru, $fase) {
            $tahun = substr($validated['tahun_ajaran'], 0, 4);
            $nomor = sprintf('RPP-%s-%04d', $tahun, (Rpp::where('tahun_ajaran', $validated['tahun_ajaran'])->count() + 1));
            $rpp = Rpp::create([
                'id_guru' => $guru->id_guru,
                'id_mapel' => $validated['id_mapel'],
                'id_rooms' => $validated['id_rooms'],
                'id_bab' => $validated['id_bab'] ?? null,
                'judul_rpp' => $validated['judul_rpp'],
                'semester' => $validated['semester'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'fase' => $fase,
                'alokasi_waktu_menit' => $validated['alokasi_jp'] * 35,
                'alokasi_waktu' => $validated['alokasi_jp'].' JP',
                'total_pertemuan' => $validated['total_pertemuan'],
                'target_jadwal' => $validated['target_jadwal'] ?? null,
                'ruang' => $validated['ruang'] ?? null,
                'target_tanggal' => $validated['target_tanggal'] ?? null,
                'nomor_rpp' => $nomor,
                'status' => 'draft',
                'komponen_checklist' => [],
            ]);
            for ($i = 1; $i <= $validated['total_pertemuan']; $i++) {
                RppSession::create(['id_rpp' => $rpp->id_rpp, 'nomor_pertemuan' => $i, 'konten' => []]);
            }

            return $rpp;
        });

        $redirect = route('guru.rpp.wizard.edit', ['id' => $rpp->id_rpp, 'step' => 1, 'pertemuan' => 1]);
        if ($request->expectsJson()) {
            return response()->json(['redirect' => $redirect, 'id_rpp' => $rpp->id_rpp]);
        }

        return redirect($redirect)->with('success', 'Draf Modul Ajar dibuat. Isi langkah-langkahnya; perubahan tersimpan otomatis.');
    }

    public function wizardEdit(Request $request, $id)
    {
        $rpp = Rpp::with(['sessions', 'mataPelajaran', 'kelas', 'guru'])->findOrFail($id);
        $this->pastikanBolehMengubah($rpp);
        abort_if(in_array($rpp->status, ['menunggu_review', 'terverifikasi'], true), 403, 'Modul yang sedang ditinjau atau disetujui tidak dapat diedit.');
        $step = max(1, min(8, (int) $request->query('step', 1)));
        $nomorPertemuan = max(1, min((int) $rpp->total_pertemuan, (int) $request->query('pertemuan', 1)));
        $guru = $rpp->guru;
        $mapelList = MataPelajaran::all();
        $kelasList = Kelas::all();
        $cpList = CapaianPembelajaran::where('id_mapel', $rpp->id_mapel)->where('fase', $this->faseCode($rpp->fase))->where('is_active', true)->orderBy('urutan')->get();
        $babList = Bab::whereIn('id_mapel', $mapelList->pluck('id_mapel'))->with('mataPelajaran')->orderBy('id_mapel')->orderBy('id_bab')->get();

        // Draf lama (sebelum wizard) belum memiliki baris pertemuan. Buat baris
        // kosong agar record lama tetap bisa dibuka dan dilengkapi tanpa migrasi
        // konten yang berisiko.
        for ($i = 1; $i <= max(1, (int) $rpp->total_pertemuan); $i++) {
            RppSession::firstOrCreate(
                ['id_rpp' => $rpp->id_rpp, 'nomor_pertemuan' => $i],
                ['konten' => []]
            );
        }
        $rppSession = RppSession::where('id_rpp', $rpp->id_rpp)->where('nomor_pertemuan', $nomorPertemuan)->firstOrFail();

        return view('rpp_guru.wizard', compact('rpp', 'guru', 'mapelList', 'kelasList', 'cpList', 'babList', 'step', 'nomorPertemuan') + ['rppSession' => $rppSession, 'isNew' => false]);
    }

    public function wizardUpdateHeader(Request $request, $id)
    {
        $rpp = Rpp::findOrFail($id);
        $this->pastikanBolehMengubah($rpp);
        abort_if(! in_array($rpp->status, ['draft', 'perlu_revisi'], true), 409, 'Modul ini tidak lagi dapat diubah.');
        $validated = $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'required|exists:kelas,id_rooms',
            'id_bab' => 'nullable|exists:bab,id_bab',
            'judul_rpp' => 'required|string|min:3|max:150',
            'semester' => 'required|in:ganjil,genap',
            'tahun_ajaran' => 'required|string|max:20|regex:/^\\d{4}\\/\\d{4}$/',
            'alokasi_jp' => 'required|integer|min:1|max:20',
            'total_pertemuan' => 'required|integer|min:1|max:20',
            'target_jadwal' => 'nullable|string|max:50',
            'ruang' => 'nullable|string|max:50',
            'target_tanggal' => 'nullable|string|max:50',
        ]);

        $this->pastikanBabSesuaiMapel($validated['id_bab'] ?? null, (int) $validated['id_mapel']);
        $fase = $this->faseRppUntukKelas(Kelas::findOrFail($validated['id_rooms']));
        if (! $fase) {
            throw ValidationException::withMessages(['id_rooms' => 'Tingkat kelas belum tersedia untuk menentukan fase pembelajaran.']);
        }

        // Jumlah pertemuan tidak boleh diturunkan setelah draf dimulai agar
        // konten yang sudah ditulis tidak hilang diam-diam.
        $total = max((int) $rpp->total_pertemuan, (int) $validated['total_pertemuan']);
        $rpp->update([
            'id_mapel' => $validated['id_mapel'],
            'id_rooms' => $validated['id_rooms'],
            'id_bab' => $validated['id_bab'] ?? null,
            'judul_rpp' => $validated['judul_rpp'],
            'semester' => $validated['semester'],
            'tahun_ajaran' => $validated['tahun_ajaran'],
            'fase' => $fase,
            'alokasi_waktu_menit' => $validated['alokasi_jp'] * 35,
            'alokasi_waktu' => $validated['alokasi_jp'].' JP',
            'total_pertemuan' => $total,
            'target_jadwal' => $validated['target_jadwal'] ?? null,
            'ruang' => $validated['ruang'] ?? null,
            'target_tanggal' => $validated['target_tanggal'] ?? null,
        ]);
        for ($i = 1; $i <= $total; $i++) {
            RppSession::firstOrCreate(['id_rpp' => $rpp->id_rpp, 'nomor_pertemuan' => $i], ['konten' => []]);
        }

        return response()->json(['saved' => true, 'saved_at' => now()->format('H:i'), 'fase' => $fase]);
    }

    public function wizardAutosave(Request $request, $id)
    {
        $rpp = Rpp::with('sessions')->findOrFail($id);
        $this->pastikanBolehMengubah($rpp);
        abort_if(! in_array($rpp->status, ['draft', 'perlu_revisi'], true), 409, 'Modul ini tidak lagi dapat diubah.');
        $validated = $request->validate([
            'pertemuan' => 'required|integer|min:1|max:'.$rpp->total_pertemuan,
            'konten' => 'required|array',
        ]);
        $session = RppSession::where('id_rpp', $rpp->id_rpp)->where('nomor_pertemuan', $validated['pertemuan'])->firstOrFail();
        $konten = $validated['konten'];
        if (! empty($konten['cp_id'])) {
            $cp = CapaianPembelajaran::where('id_cp', $konten['cp_id'])
                ->where('id_mapel', $rpp->id_mapel)
                ->where('fase', $this->faseCode($rpp->fase))
                ->where('is_active', true)
                ->first();
            if (! $cp) {
                throw ValidationException::withMessages(['konten.cp_id' => 'CP tidak cocok dengan mata pelajaran dan fase RPP ini.']);
            }
            $konten['cp_text'] = $cp->cp_text;
            $konten['cp_elemen'] = $cp->elemen;
        }
        $session->update(['konten' => $konten]);

        return response()->json(['saved' => true, 'saved_at' => now()->format('H:i'), 'status' => $rpp->status]);
    }

    public function wizardUploadMedia(Request $request, $id)
    {
        $rpp = Rpp::with('sessions')->findOrFail($id);
        $this->pastikanBolehMengubah($rpp);
        abort_if(! in_array($rpp->status, ['draft', 'perlu_revisi'], true), 409, 'Modul ini tidak lagi dapat diubah.');
        $validated = $request->validate([
            'pertemuan' => 'required|integer|min:1|max:'.$rpp->total_pertemuan,
            'media_files' => 'required|array|max:10',
            'media_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $session = RppSession::where('id_rpp', $rpp->id_rpp)->where('nomor_pertemuan', $validated['pertemuan'])->firstOrFail();
        $content = (array) $session->konten;
        $files = $content['file_uploads'] ?? [];
        foreach ($request->file('media_files', []) as $file) {
            $path = $file->store('rpp_media/'.$rpp->id_rpp, 'public');
            $files[] = ['name' => $file->getClientOriginalName(), 'path' => $path, 'mime' => $file->getMimeType(), 'size' => $file->getSize()];
        }
        $content['file_uploads'] = $files;
        $session->update(['konten' => $content]);

        return response()->json(['uploaded' => count($files), 'files' => $files]);
    }

    public function wizardSubmit(Request $request, $id)
    {
        $rpp = Rpp::with('sessions')->findOrFail($id);
        $this->pastikanBolehMengubah($rpp);
        abort_if(! in_array($rpp->status, ['draft', 'perlu_revisi'], true), 409, 'Modul ini tidak dapat diajukan dalam status saat ini.');

        $errors = [];
        foreach ($rpp->sessions as $session) {
            $content = (array) $session->konten;
            $tujuan = collect($content['tp_list'] ?? [])->filter(fn ($tp) => trim((string) ($tp['teks'] ?? '')) !== '');
            if ($tujuan->count() < 3) {
                $errors["pertemuan_{$session->nomor_pertemuan}_tp"] = "Pertemuan {$session->nomor_pertemuan}: isi minimal 3 tujuan pembelajaran.";
            }
            foreach ($tujuan as $index => $tp) {
                if (empty($tp['dimensi'])) {
                    $errors["pertemuan_{$session->nomor_pertemuan}_dimensi"] = "Pertemuan {$session->nomor_pertemuan}: setiap TP harus memiliki dimensi Profil Pelajar Pancasila.";
                    break;
                }
            }
            if (empty(array_filter($content['pertanyaan_pemantik'] ?? []))) {
                $errors["pertemuan_{$session->nomor_pertemuan}_pemantik"] = "Pertemuan {$session->nomor_pertemuan}: isi minimal satu pertanyaan pemantik.";
            }
            if (empty($content['cp_id'])) {
                $errors["pertemuan_{$session->nomor_pertemuan}_cp"] = "Pertemuan {$session->nomor_pertemuan}: pilih Capaian Pembelajaran dari master CP.";
            }
            if (empty(trim((string) ($content['langkah_pendahuluan'] ?? ''))) || empty(trim((string) ($content['langkah_penutup'] ?? '')))) {
                $errors["pertemuan_{$session->nomor_pertemuan}_langkah"] = "Pertemuan {$session->nomor_pertemuan}: lengkapi uraian pendahuluan dan penutup.";
            }
            if (empty($content['asesmen_formatif']['instrumen'] ?? '') && empty($content['asesmen_sumatif']['instrumen'] ?? '')) {
                $errors["pertemuan_{$session->nomor_pertemuan}_asesmen"] = "Pertemuan {$session->nomor_pertemuan}: lengkapi instrumen asesmen formatif atau sumatif.";
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $rpp->update(['status' => 'menunggu_review']);
        RppCatatan::create([
            'id_rpp' => $rpp->id_rpp,
            'penulis_role' => 'guru',
            'penulis_id' => session('user_id'),
            'penulis_nama' => session('user_name') ?? 'Guru',
            'status_saat_itu' => 'menunggu_review',
            'isi' => 'Modul Ajar diajukan untuk ditinjau Admin.',
        ]);
        $this->simpanVersiRpp($rpp->fresh('sessions'), 'Diajukan untuk review');

        return redirect()->route('guru.rpp.index')->with('success', 'Modul Ajar berhasil diajukan dan menunggu tinjauan Admin.');
    }

    public function adminCpIndex()
    {
        $cpList = CapaianPembelajaran::with('mataPelajaran')->orderBy('id_mapel')->orderBy('fase')->orderBy('urutan')->paginate(20);
        $mapelList = MataPelajaran::orderBy('nama_mapel')->get();

        return view('rpp.cp_index', compact('cpList', 'mapelList'));
    }

    public function adminCpStore(Request $request)
    {
        $data = $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'fase' => 'required|in:A,B',
            'elemen' => 'nullable|string|max:150',
            'cp_text' => 'required|string|max:10000',
            'urutan' => 'nullable|integer|min:0',
        ]);
        CapaianPembelajaran::create($data + ['is_active' => true]);

        return back()->with('success', 'Capaian Pembelajaran berhasil ditambahkan.');
    }

    public function adminCpUpdate(Request $request, $id)
    {
        $cp = CapaianPembelajaran::findOrFail($id);
        $data = $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'fase' => 'required|in:A,B',
            'elemen' => 'nullable|string|max:150',
            'cp_text' => 'required|string|max:10000',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);
        $cp->update($data);

        return back()->with('success', 'Capaian Pembelajaran berhasil diperbarui.');
    }

    private function faseRppUntukKelas(Kelas $kelas): ?string
    {
        $tingkat = (int) ($kelas->tingkat ?: (preg_match('/\\d+/', (string) $kelas->pararel, $m) ? ($m[0] ?? 0) : 0));

        return match (true) {
            $tingkat >= 1 && $tingkat <= 3 => 'Fase A',
            $tingkat >= 4 && $tingkat <= 6 => 'Fase B',
            default => null,
        };
    }

    private function faseCode(?string $fase): ?string
    {
        return preg_match('/\\b([AB])\\b/i', (string) $fase, $matches) ? strtoupper($matches[1]) : null;
    }

    private function simpanVersiRpp(Rpp $rpp, string $perubahan): void
    {
        $versi = ((int) $rpp->versions()->max('versi')) + 1;
        $rpp->versions()->create([
            'versi' => $versi,
            'snapshot' => $rpp->toArray(),
            'perubahan_apa' => $perubahan,
            'dibuat_oleh' => session('user_id'),
        ]);
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
                    null,
                ];
            }

            // 2. Cek durasi minimal (1 JP = 45 menit)
            if ($durasi < 45) {
                return [
                    false,
                    sprintf('Durasi pembelajaran pada jadwal (%d menit) terlalu singkat! Minimal 1 JP (45 menit).', $durasi),
                    null,
                ];
            }

            // 3. Cek durasi maksimal (maksimal 4-5 JP / 225 menit untuk 1 modul ajar)
            if ($durasi > 225) {
                return [
                    false,
                    sprintf('Durasi pembelajaran pada jadwal (%d menit) terlalu panjang untuk satu modul ajar (maksimal 5 JP / 225 menit).', $durasi),
                    null,
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
        if (session('user_type') === 'guru') {
            // Guru selalu membuat RPP atas namanya sendiri; id_guru dari form
            // tidak boleh dipakai untuk mewakili guru lain.
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
            'id_bab' => 'nullable|exists:bab,id_bab',
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
            'asesmen_teknik' => 'nullable|array|max:6',
            'asesmen_teknik.*' => 'string|max:50',
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

        $validated['komponen_checklist'] = $this->susunChecklistKomponen($request);

        // Fase tidak boleh diketik manual: selalu diturunkan dari kelas agar
        // dokumen konsisten dengan jenjang (A: I-II, B: III-IV, C: V-VI).
        $validated['fase'] = $this->faseUntukKelas($validated['id_rooms'] ?? null);

        $validated['status'] = session('user_type') === 'guru' || empty($validated['status'])
            ? ($request->input('action') === 'draft' ? 'draft' : 'menunggu_review')
            : $validated['status'];

        $this->pastikanBabSesuaiMapel($validated['id_bab'] ?? null, (int) $validated['id_mapel']);
        $this->pastikanKomponenWajib($validated['komponen_checklist'], $validated['status'], $validated['id_bab'] ?? null);

        Rpp::create($validated);

        $redirectRoute = session('user_type') === 'guru' ? route('guru.rpp.index') : route('rpp.index');
        if ($validated['status'] === 'menunggu_review') {
            return redirect($redirectRoute)->with('success', 'Modul Ajar RPP berhasil dikirim. Status saat ini: "Menunggu Persetujuan Admin" dan belum bisa diaplikasikan hingga disetujui (di-accept) oleh Administrator.');
        }

        return redirect($redirectRoute)->with('success', 'Draf Modul Ajar RPP berhasil disimpan.');
    }

    /**
     * Susun komponen_checklist dari input form. Kunci lama (kktp,
     * soal_proyektor, audio, tags) tetap disimpan agar dokumen lama tidak
     * kehilangan data saat diedit.
     */
    private function susunChecklistKomponen(Request $request): array
    {
        $customTags = [];
        if ($request->filled('custom_tags')) {
            $customTags = is_array($request->custom_tags)
                ? $request->custom_tags
                : array_filter(array_map('trim', explode(',', $request->custom_tags)));
        }

        $komponen = [];
        foreach (array_keys(Rpp::KOMPONEN_WAJIB + Rpp::KOMPONEN_DISARANKAN) as $kunci) {
            $komponen[$kunci] = $request->boolean($kunci);
        }

        return $komponen + [
            'kktp' => $request->boolean('kktp'),
            'soal_proyektor' => $request->boolean('soal_proyektor'),
            'audio' => $request->boolean('audio'),
            'asesmen_teknik' => array_values(array_filter((array) $request->input('asesmen_teknik', []))),
            'tags' => $customTags,
        ];
    }

    /** Fase diturunkan dari tingkat kelas; null bila kelas tidak dipilih. */
    private function faseUntukKelas(?int $idRooms): ?string
    {
        if (! $idRooms) {
            return null;
        }

        return Kelas::find($idRooms)?->fase();
    }

    /** Bab yang dipilih harus milik mata pelajaran yang sama. */
    private function pastikanBabSesuaiMapel(?int $idBab, int $idMapel): void
    {
        if (! $idBab) {
            return;
        }

        $bab = Bab::find($idBab);
        if (! $bab || (int) $bab->id_mapel !== $idMapel) {
            abort(422, 'Bab yang dipilih bukan bagian dari mata pelajaran tersebut.');
        }
    }

    /**
     * Saat diajukan (bukan draf), dokumen harus punya bab, tiga komponen wajib,
     * dan minimal dua teknik asesmen berbeda — turunan Permendikdasmen 1/2026
     * Pasal 5, 8, dan 10. Draf tetap boleh disimpan belum lengkap.
     */
    private function pastikanKomponenWajib(array $komponen, string $status, ?int $idBab): void
    {
        if ($status !== 'menunggu_review') {
            return;
        }

        $kurang = collect(Rpp::KOMPONEN_WAJIB)
            ->reject(fn ($label, $kunci) => (bool) ($komponen[$kunci] ?? false))
            ->values();

        $errors = [];
        if (! $idBab) {
            $errors['id_bab'] = 'Pilih bab yang diajarkan sebelum mengajukan RPP.';
        }
        if ($kurang->isNotEmpty()) {
            $errors['komponen'] = 'Komponen wajib belum lengkap: '.$kurang->implode(', ').'.';
        }

        $teknik = array_filter((array) ($komponen['asesmen_teknik'] ?? []));
        if (count($teknik) < 2) {
            $errors['asesmen_teknik'] = 'Pilih minimal dua teknik asesmen yang berbeda.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
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

        $this->pastikanBolehMelihat($rpp);

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

        $this->pastikanBolehMengubah($rpp);

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
            'id_bab' => 'nullable|exists:bab,id_bab',
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
            'asesmen_teknik' => 'nullable|array|max:6',
            'asesmen_teknik.*' => 'string|max:50',
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

        $checklistLama = (array) ($rpp->komponen_checklist ?? []);

        $customTags = $checklistLama['tags'] ?? [];
        if ($request->has('custom_tags')) {
            $customTags = is_array($request->custom_tags)
                ? $request->custom_tags
                : array_filter(array_map('trim', explode(',', $request->custom_tags)));
        }

        // Checkbox yang tidak dicentang tidak ikut terkirim, jadi nilai lama
        // dipertahankan untuk kunci yang memang tidak ada di request.
        $validated['komponen_checklist'] = $this->susunChecklistKomponen($request)
            + array_intersect_key($checklistLama, Rpp::KOMPONEN_WAJIB + Rpp::KOMPONEN_DISARANKAN);
        $validated['komponen_checklist']['tags'] = $customTags;

        // Fase selalu diturunkan dari kelas, bukan dari input bebas.
        $validated['fase'] = $this->faseUntukKelas($validated['id_rooms'] ?? null);

        if (session('user_type') === 'guru') {
            // Jika guru menyimpan sebagai draf, status draf. Jika diajukan, status menunggu_review agar admin menerima/menolak
            $validated['status'] = $request->input('action') === 'draft' ? 'draft' : 'menunggu_review';
        } elseif ($request->filled('status')) {
            $validated['status'] = $request->status;
        }

        $this->pastikanBabSesuaiMapel($validated['id_bab'] ?? null, (int) $validated['id_mapel']);
        $this->pastikanKomponenWajib(
            $validated['komponen_checklist'],
            $validated['status'] ?? $rpp->status,
            $validated['id_bab'] ?? $rpp->id_bab
        );

        $rpp->update($validated);

        $redirectRoute = session('user_type') === 'guru' ? route('guru.rpp.index') : route('rpp.index');
        if (session('user_type') === 'guru' && ($validated['status'] ?? '') === 'menunggu_review') {
            return redirect($redirectRoute)->with('success', 'Perubahan RPP berhasil dikirim. Status: "Menunggu Persetujuan Admin" dan belum bisa diaplikasikan hingga disetujui (di-accept) oleh Administrator.');
        }

        return redirect($redirectRoute)->with('success', 'RPP sukses diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        // Route sudah dibatasi admin; penjagaan ini menjaga bila pemanggilan
        // terjadi dari jalur lain di masa depan.
        if (session('user_type') !== 'admin') {
            abort(403, 'Hanya administrator yang dapat mengubah status supervisi RPP.');
        }

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

        // Simpan catatan sebagai riwayat, bukan menimpa catatan sebelumnya.
        if (trim((string) $catatan) !== '') {
            RppCatatan::create([
                'id_rpp' => $rpp->id_rpp,
                'penulis_role' => 'admin',
                'penulis_id' => session('user_id'),
                'penulis_nama' => session('user_name') ?? 'Administrator',
                'status_saat_itu' => $request->status,
                'isi' => trim((string) $catatan),
            ]);
        }

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
     * Guru hanya boleh mengubah/menghapus RPP miliknya sendiri.
     * Admin (supervisor) boleh menindak semua RPP.
     */
    private function pastikanBolehMengubah(Rpp $rpp): void
    {
        if (session('user_type') !== 'guru') {
            return;
        }

        if ((int) $rpp->id_guru !== (int) session('user_id')) {
            abort(403, 'Anda hanya dapat mengubah RPP milik Anda sendiri.');
        }
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

        $this->pastikanBolehMengubah($rpp);

        if ($rpp->file_rpp) {
            Storage::disk('public')->delete($rpp->file_rpp);
        }
        $rpp->delete();

        return back()->with('success', 'RPP sukses dihapus.');
    }

    public function download($id)
    {
        $rpp = Rpp::with(['guru', 'mataPelajaran', 'kelas', 'sessions'])->where('id_rpp', $id)->first();
        if (! $rpp) {
            abort(404, 'Berkas RPP tidak ditemukan.');
        }

        $this->pastikanBolehMelihat($rpp);

        if ($rpp->file_rpp && Storage::disk('public')->exists($rpp->file_rpp)) {
            return response()->download(storage_path('app/public/'.$rpp->file_rpp), basename($rpp->file_rpp));
        }

        // Jika belum ada berkas upload fisik, buka dokumen resmi Modul Ajar siap cetak & simpan PDF
        return view('rpp_guru.cetak', compact('rpp'));
    }

    /**
     * Guru hanya boleh membaca/mengunduh RPP miliknya; admin (supervisor)
     * boleh membaca semua dokumen untuk keperluan verifikasi.
     */
    private function pastikanBolehMelihat(Rpp $rpp): void
    {
        if (session('user_type') !== 'guru') {
            return;
        }

        if ((int) $rpp->id_guru !== (int) session('user_id')) {
            abort(403, 'Anda hanya dapat mengakses RPP milik Anda sendiri.');
        }
    }
}
