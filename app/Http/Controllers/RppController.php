<?php

namespace App\Http\Controllers;

use App\Models\Rpp;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Guru;
use App\Models\JadwalMataPelajaran;
use App\Models\Notifikasi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

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
                    ->orWhereHas('guru', fn($g) => $g->where('nama_guru', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%"));
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
        if (!$guru) {
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
                    ->orWhereHas('mataPelajaran', fn($m) => $m->where('nama_mapel', 'like', "%{$search}%"));
            });
        }

        $rppList = $query->latest('id_rpp')->paginate(12)->withQueryString();
        $mapelList = MataPelajaran::all();
        $kelasList = Kelas::all();
        $jadwalList = $guru ? JadwalMataPelajaran::with(['mataPelajaran', 'kelas'])->where('id_guru', $guru->id_guru)->get() : collect();

        return view('rpp-guru.index', compact(
            'rppList',
            'kpiGuru',
            'mapelList',
            'kelasList',
            'jadwalList',
            'guru',
            'tab'
        ));
    }

    public function store(Request $request)
    {
        if (session('user_type') === 'admin') {
            return back()->with('error', 'Administrator tidak dapat menambahkan RPP langsung. RPP diajukan oleh guru untuk diverifikasi.');
        }

        $idGuru = $request->input('id_guru');
        if (!$idGuru && session('user_type') === 'guru') {
            $idGuru = session('user_id');
        }
        if (!$idGuru) {
            $idGuru = Guru::value('id_guru');
        }
        $request->merge(['id_guru' => $idGuru]);

        $validated = $request->validate([
            'id_guru'       => 'required|exists:guru,id_guru',
            'id_mapel'      => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms'      => 'nullable|exists:kelas,id_rooms',
            'id_jadwal'     => 'nullable|exists:jadwal_mata_pelajaran,id_jadwal',
            'judul_rpp'     => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'deskripsi'     => ['nullable', 'string', 'max:500'],
            'alokasi_waktu' => ['nullable', 'string', 'max:4'],
            'fase'          => ['nullable', 'string', 'max:20'],
            'modul_ke'      => ['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\s]+$/u'],
            'target_jadwal' => ['nullable', 'string', 'max:50'],
            'ruang'         => ['nullable', 'string', 'max:50'],
            'target_tanggal'=> 'nullable|string|max:50',
            'custom_tags'   => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;
                    $tags = is_array($value) ? $value : array_filter(array_map('trim', explode(',', $value)));
                    foreach ($tags as $tag) {
                        if (!preg_match('/^[a-zA-Z0-9\s]+$/u', $tag)) {
                            $fail('Label / Tag hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).');
                            return;
                        }
                    }
                }
            ],
            'status'        => 'nullable|in:draft,menunggu_review,terverifikasi,perlu_revisi',
            'file_rpp'      => 'nullable|file|mimes:pdf,docx,doc|max:10240',
        ], [
            'judul_rpp.required' => 'Topik / Judul Modul Ajar wajib diisi.',
            'judul_rpp.min'      => 'Topik / Judul Modul Ajar minimal 3 karakter.',
            'judul_rpp.max'      => 'Topik / Judul Modul Ajar maksimal 100 karakter.',
            'judul_rpp.regex'    => 'Topik / Judul Modul Ajar hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'modul_ke.max'       => 'Nomor Modul atau Bab maksimal 20 karakter.',
            'modul_ke.regex'     => 'Nomor Modul atau Bab hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'alokasi_waktu.max'  => 'Alokasi Waktu maksimal 4 karakter (Contoh: 2 JP. Catatan: 1JP = 45 Menit).',
            'target_jadwal.max'  => 'Target Roster Jadwal maksimal 50 karakter.',
            'ruang.max'          => 'Ruang / Lokasi Belajar maksimal 50 karakter.',
            'deskripsi.max'      => 'Capaian Pembelajaran / Deskripsi maksimal 500 karakter.',
            'file_rpp.mimes'     => 'Format berkas RPP & Modul hanya boleh berupa dokumen PDF (.pdf) atau Word (.docx, .doc).',
            'file_rpp.max'       => 'Ukuran berkas RPP & Modul maksimal 10MB.',
        ]);

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
        if (!$rpp) {
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
        if (!$rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }

        $validated = $request->validate([
            'id_mapel'      => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms'      => 'nullable|exists:kelas,id_rooms',
            'id_jadwal'     => 'nullable|exists:jadwal_mata_pelajaran,id_jadwal',
            'judul_rpp'     => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'deskripsi'     => ['nullable', 'string', 'max:500'],
            'alokasi_waktu' => ['nullable', 'string', 'max:4'],
            'fase'          => ['nullable', 'string', 'max:20'],
            'modul_ke'      => ['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\s]+$/u'],
            'target_jadwal' => ['nullable', 'string', 'max:50'],
            'ruang'         => ['nullable', 'string', 'max:50'],
            'target_tanggal'=> 'nullable|string|max:50',
            'custom_tags'   => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;
                    $tags = is_array($value) ? $value : array_filter(array_map('trim', explode(',', $value)));
                    foreach ($tags as $tag) {
                        if (!preg_match('/^[a-zA-Z0-9\s]+$/u', $tag)) {
                            $fail('Label / Tag hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).');
                            return;
                        }
                    }
                }
            ],
            'status'        => 'nullable|in:draft,menunggu_review,terverifikasi,perlu_revisi',
            'file_rpp'      => 'nullable|file|mimes:pdf,docx,doc|max:10240',
        ], [
            'judul_rpp.required' => 'Topik / Judul Modul Ajar wajib diisi.',
            'judul_rpp.min'      => 'Topik / Judul Modul Ajar minimal 3 karakter.',
            'judul_rpp.max'      => 'Topik / Judul Modul Ajar maksimal 100 karakter.',
            'judul_rpp.regex'    => 'Topik / Judul Modul Ajar hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'modul_ke.max'       => 'Nomor Modul atau Bab maksimal 20 karakter.',
            'modul_ke.regex'     => 'Nomor Modul atau Bab hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol).',
            'alokasi_waktu.max'  => 'Alokasi Waktu maksimal 4 karakter (Contoh: 2 JP. Catatan: 1JP = 45 Menit).',
            'target_jadwal.max'  => 'Target Roster Jadwal maksimal 50 karakter.',
            'ruang.max'          => 'Ruang / Lokasi Belajar maksimal 50 karakter.',
            'deskripsi.max'      => 'Capaian Pembelajaran / Deskripsi maksimal 500 karakter.',
            'file_rpp.mimes'     => 'Format berkas RPP & Modul hanya boleh berupa dokumen PDF (.pdf) atau Word (.docx, .doc).',
            'file_rpp.max'       => 'Ukuran berkas RPP & Modul maksimal 10MB.',
        ]);

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
        if (!$rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }

        $catatan = $request->input('catatan_revisi') ?? $request->input('catatan');

        $rpp->update([
            'status' => $request->status,
            'catatan_revisi' => $catatan,
        ]);

        $pesanSukses = match($request->status) {
            'terverifikasi' => "Permintaan RPP '{$rpp->judul_rpp}' berhasil diterima dan disetujui.",
            'perlu_revisi'  => "Permintaan RPP '{$rpp->judul_rpp}' dikembalikan ke guru untuk revisi.",
            default => "Status RPP berhasil diperbarui.",
        };

        return back()->with('success', $pesanSukses);
    }

    public function destroy($id)
    {
        $rpp = Rpp::where('id_rpp', $id)->first();
        if (!$rpp) {
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
        if (!$rpp) {
            abort(404, 'Berkas RPP tidak ditemukan.');
        }

        if ($rpp->file_rpp && Storage::disk('public')->exists($rpp->file_rpp)) {
            return response()->download(storage_path('app/public/' . $rpp->file_rpp), basename($rpp->file_rpp));
        }

        // Jika belum ada berkas upload fisik, buat dokumen ringkasan format text / docx siap cetak
        $content = "RENCANA PELAKSANAAN PEMBELAJARAN (MODUL AJAR)\n";
        $content .= "KURIKULUM MERDEKA - SDN KALITAPEN 01\n";
        $content .= "=========================================================\n\n";
        $content .= "Topik / Judul: " . $rpp->judul_rpp . "\n";
        $content .= "Fase & Modul: " . ($rpp->fase ?? 'Fase B') . " • " . ($rpp->modul_ke ?? 'Modul #' . $rpp->id_rpp) . "\n";
        $content .= "Mata Pelajaran: " . ($rpp->mataPelajaran->nama_mapel ?? '-') . "\n";
        $content .= "Kelas: " . ($rpp->kelas->pararel ?? 'Kelas 4B') . "\n";
        $content .= "Guru Pengajar: " . ($rpp->guru->nama_guru ?? '-') . " (NIP. " . ($rpp->guru->nip ?? '-') . ")\n";
        $content .= "Alokasi Waktu: " . ($rpp->alokasi_waktu ?? '2 JP') . "\n";
        $content .= "Jadwal & Ruang: " . ($rpp->target_jadwal ?? '-') . " (" . ($rpp->ruang ?? 'Ruang Kelas 4B') . ")\n\n";
        $content .= "CAPAIAN PEMBELAJARAN (TP):\n";
        $content .= ($rpp->deskripsi ?? 'Peserta didik memahami konsep pembelajaran secara komprehensif.') . "\n\n";
        $content .= "STATUS SUPERVISI: " . strtoupper($rpp->status) . "\n";
        $content .= "Dicetak pada: " . now()->format('d F Y H:i') . " WIB\n";

        $fileName = 'RPP_' . \Illuminate\Support\Str::slug($rpp->judul_rpp) . '.txt';
        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}