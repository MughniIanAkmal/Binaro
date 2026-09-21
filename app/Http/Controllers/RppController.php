<?php

namespace App\Http\Controllers;

use App\Models\Rpp;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RppController extends Controller
{
    public function index(Request $request)
    {
        $query = Rpp::with(['guru', 'mataPelajaran']);

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
                    ->orWhereHas('guru', fn($g) => $g->where('nama_guru', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%"));
            });
        }

        $rppList = $query->latest('created_at')->paginate(10)->withQueryString();
        $mapelList = MataPelajaran::all();
        $kelasList = Kelas::all();
        $namaGuru = \App\Models\Guru::all();

        return view('rpp.index', compact('rppList', 'kpi', 'mapelList', 'kelasList', 'namaGuru'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_guru' => 'required|exists:guru,id_guru',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
            'judul_rpp' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'file_rpp' => 'nullable|file|mimes:pdf,docx,doc|max:10240',
        ]);

        if ($request->hasFile('file_rpp')) {
            $validated['file_rpp'] = $request->file('file_rpp')->store('rpp_files', 'public');
        }

        $validated['komponen_checklist'] = [
            'tujuan' => $request->boolean('tujuan'),
            'video' => $request->boolean('video'),
            'kktp' => $request->boolean('kktp'),
            'lkpd' => $request->boolean('lkpd'),
        ];
        $validated['status'] = 'menunggu_review';

        Rpp::create($validated);
        return back()->with('success', 'Modul Ajar RPP sukses diunggah.');
    }

    public function show($id)
    {
        $rpp = Rpp::with(['guru', 'mataPelajaran'])->where('id_rpp', $id)->first();
        if (!$rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }
        return view('rpp.show', compact('rpp'));
    }

    public function update(Request $request, $id)
    {
        $rpp = Rpp::where('id_rpp', $id)->first();
        if (!$rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }
        if (!in_array($rpp->status, ['draft', 'perlu_revisi'])) {
            return back()->with('error', 'RPP sudah diverifikasi, tidak dapat diubah.');
        }

        $validated = $request->validate([
            'id_guru' => 'required|exists:guru,id_guru',
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
            'judul_rpp' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'file_rpp' => 'nullable|file|mimes:pdf,docx,doc|max:10240',
        ]);

        if ($request->hasFile('file_rpp')) {
            if ($rpp->file_rpp) {
                Storage::disk('public')->delete($rpp->file_rpp);
            }
            $validated['file_rpp'] = $request->file('file_rpp')->store('rpp_files', 'public');
        }

        $validated['komponen_checklist'] = [
            'tujuan' => $request->boolean('tujuan'),
            'video' => $request->boolean('video'),
            'kktp' => $request->boolean('kktp'),
            'lkpd' => $request->boolean('lkpd'),
        ];

        $rpp->update($validated);
        return back()->with('success', 'RPP sukses diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,menunggu_review,terverifikasi,perlu_revisi',
            'catatan' => 'nullable|string'
        ]);

        $rpp = Rpp::where('id_rpp', $id)->first();
        if (!$rpp) {
            abort(404, 'RPP tidak ditemukan.');
        }

        $rpp->update([
            'status' => $request->status,
            'catatan_revisi' => $request->catatan,
        ]);

        Notifikasi::create([
            'id_guru' => $rpp->id_guru,
            'id_siswa' => 1,
            'pesan' => "Status RPP '{$rpp->judul_rpp}' diperbarui menjadi: " . strtoupper($request->status),
            'status_baca' => false,
        ]);

        return back()->with('success', 'Status RPP sukses diperbarui.');
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
        $rpp = Rpp::where('id_rpp', $id)->first();
        if (!$rpp || !$rpp->file_rpp) {
            abort(404, 'Berkas RPP tidak ditemukan.');
        }
        return response()->download(storage_path('app/public/' . $rpp->file_rpp), basename($rpp->file_rpp));
    }
}