<?php

namespace App\Http\Controllers;

use App\Models\Bab;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Quiz;
use App\Models\SoalQuiz;
use App\Models\SubBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    public function index(Request $request)
    {
        $mapels = MataPelajaran::all();
        $selectedMapelId = $request->query('mapel_id', $mapels->first()?->id_mapel);

        $babs = collect();
        if ($selectedMapelId) {
            $babs = Bab::with(['subBab.materi.quiz'])
                       ->where('id_mapel', $selectedMapelId)
                       ->get();
        }

        return view('guru.materi.index', compact('mapels', 'selectedMapelId', 'babs'));
    }

    // 4.1 Hierarchy: Grid Mapel
    public function mapelGrid()
    {
        $mapels = MataPelajaran::withCount(['bab'])->get();
        return view('guru.mapel.index', compact('mapels'));
    }

    // 4.1 Hierarchy: List Bab per Mapel
    public function babList($idMapel)
    {
        $mapel = MataPelajaran::with(['bab.subBab.materi'])->findOrFail($idMapel);
        return view('guru.bab.index', compact('mapel'));
    }

    // 4.1 Hierarchy: List Sub-Bab per Bab
    public function subBabList($idBab)
    {
        $bab = Bab::with(['mataPelajaran', 'subBab.materi.quiz'])->findOrFail($idBab);
        return view('guru.sub_bab.index', compact('bab'));
    }

    // 4.1 Hierarchy: Materi under Sub-Bab
    public function materiList($idSubBab)
    {
        $subBab = SubBab::with(['bab.mataPelajaran', 'materi.quiz'])->findOrFail($idSubBab);
        return view('guru.materi.sub_bab_view', compact('subBab'));
    }

    // Cascading Dropdown API Helpers for Modal Action (Tambah / Edit)
    public function getBabsByMapel($idMapel)
    {
        $babs = Bab::where('id_mapel', $idMapel)->get(['id_bab', 'nama_bab']);
        return response()->json($babs);
    }

    public function getSubBabsByBab($idBab)
    {
        $subBabs = SubBab::where('id_bab', $idBab)->get(['id_sub_bab', 'nama_sub_bab']);
        return response()->json($subBabs);
    }

    public function getMaterisBySubBab($idSubBab)
    {
        $materis = Materi::where('id_sub_bab', $idSubBab)->get(['id_materi', 'judul_materi', 'tipe_materi']);
        return response()->json($materis);
    }

    public function getMateriDetail($idMateri)
    {
        $materi = Materi::with(['subBab.bab.mataPelajaran', 'quiz'])->findOrFail($idMateri);
        return response()->json($materi);
    }

    public function storeBab(Request $request)
    {
        $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'nama_bab' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
        ], [
            'nama_bab.required' => 'Nama Bab wajib diisi.',
            'nama_bab.min'      => 'Nama Bab minimal terdiri dari 3 karakter.',
            'nama_bab.max'      => 'Nama Bab maksimal 100 karakter.',
            'nama_bab.regex'    => 'Nama Bab hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol atau karakter khusus).',
        ]);

        // Guardrail: Anti-duplikasi nama bab di mapel sama (case-insensitive & trimmed)
        $exists = Bab::where('id_mapel', $request->id_mapel)
                     ->whereRaw('LOWER(TRIM(nama_bab)) = ?', [mb_strtolower(trim($request->nama_bab))])
                     ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Nama Bab sudah ada di Mata Pelajaran ini. Silakan gunakan nama bab lain.');
        }

        Bab::create([
            'id_mapel' => $request->id_mapel,
            'nama_bab' => trim($request->nama_bab),
        ]);

        return back()->with('success', 'Bab berhasil ditambahkan.');
    }

    public function storeSubBab(Request $request)
    {
        $request->validate([
            'id_bab' => 'required|exists:bab,id_bab',
            'nama_sub_bab' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
        ], [
            'nama_sub_bab.required' => 'Nama Sub-Bab wajib diisi.',
            'nama_sub_bab.min'      => 'Nama Sub-Bab minimal terdiri dari 3 karakter.',
            'nama_sub_bab.max'      => 'Nama Sub-Bab maksimal 100 karakter.',
            'nama_sub_bab.regex'    => 'Nama Sub-Bab hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol atau karakter khusus).',
        ]);

        // Guardrail: Anti-duplikasi nama sub-bab di bab sama (case-insensitive & trimmed)
        $exists = SubBab::where('id_bab', $request->id_bab)
                        ->whereRaw('LOWER(TRIM(nama_sub_bab)) = ?', [mb_strtolower(trim($request->nama_sub_bab))])
                        ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Nama Sub-Bab sudah ada di Bab ini. Silakan gunakan nama sub-bab lain.');
        }

        SubBab::create([
            'id_bab' => $request->id_bab,
            'nama_sub_bab' => trim($request->nama_sub_bab),
        ]);

        return back()->with('success', 'Sub-Bab berhasil ditambahkan.');
    }

    public function storeMateri(Request $request)
    {
        $videoValidator = function ($attribute, $value, $fail) use ($request) {
            if ($request->tipe_materi === 'video' && $value) {
                $val = trim($value);
                if (preg_match('#(drive\.google\.com|docs\.google\.com|google\.com/file|google\.com/drive|dropbox\.com|onedrive\.live\.com)#i', $val)) {
                    $fail('Tautan Google Drive atau penyimpanan cloud lainnya dilarang. Harap gunakan tautan YouTube atau file video langsung (.mp4).');
                    return;
                }
                $isYt = (bool) (preg_match('#^(https?:\/\/)?(www\.|m\.)?(youtube(?:-nocookie)?\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtu\.be\/)[a-zA-Z0-9_\-]+#i', $val) || preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts|live)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $val));
                $isMp4 = (bool) preg_match('#^https?:\/\/[^\s]+\.mp4(\?[^\s]*)?$#i', $val);

                if (!$isYt && !$isMp4) {
                    $fail('URL Video pembelajaran HANYA boleh berupa tautan YouTube (youtube.com / youtu.be) atau file video langsung berformat MP4 (.mp4). Link lain seperti Google Drive dilarang.');
                }
            }
        };

        $excelValidator = function ($attribute, $value, $fail) use ($request) {
            if ($request->tipe_materi === 'kuis' && $value) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                    $fail('File kuis wajib berformat spreadsheet Excel / CSV (.xlsx, .xls, atau .csv). Format file lainnya tidak diperbolehkan.');
                }
            }
        };

        $request->validate([
            'id_sub_bab'   => 'required|exists:sub_bab,id_sub_bab',
            'judul_materi' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'tipe_materi'  => 'required|in:video,dokumen,kuis',
            'url_video'    => ['nullable', 'required_if:tipe_materi,video', 'max:255', $videoValidator],
            'file_pdf'     => 'nullable|required_if:tipe_materi,dokumen|file|mimes:pdf|max:10240', // Max 10MB
            'file_excel'   => ['nullable', 'file', 'max:5120', $excelValidator],
            'isi_materi'   => 'nullable|string',
        ], [
            'judul_materi.required' => 'Nama materi wajib diisi.',
            'judul_materi.min'      => 'Nama materi minimal 3 karakter.',
            'judul_materi.max'      => 'Nama materi maksimal 100 karakter.',
            'judul_materi.regex'    => 'Nama materi hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol atau karakter khusus).',
            'url_video.required_if' => 'URL Video wajib diisi jika memilih tipe materi Video.',
            'file_pdf.required_if'  => 'File dokumen PDF wajib diunggah jika memilih tipe Dokumen.',
            'file_pdf.mimes'        => 'File materi harus berformat PDF (.pdf).',
        ]);

        $subBab = SubBab::findOrFail($request->id_sub_bab);

        // Guardrail: Anti-duplikasi judul materi di sub-bab yang sama
        $duplicateMateri = Materi::where('id_sub_bab', $request->id_sub_bab)
            ->whereRaw('LOWER(TRIM(judul_materi)) = ?', [mb_strtolower(trim($request->judul_materi))])
            ->exists();
        if ($duplicateMateri) {
            return back()->withInput()->with('error', 'Materi dengan judul yang sama sudah ada di Sub-Bab ini. Silakan gunakan judul materi lain.');
        }

        $filePdfPath = null;
        $idQuiz = null;

        if ($request->tipe_materi === 'dokumen' && $request->hasFile('file_pdf')) {
            $filePdfPath = $request->file('file_pdf')->store('materi_pdf', 'public');
        }

        if ($request->tipe_materi === 'kuis') {
            $quiz = Quiz::create([
                'id_sub_bab' => $subBab->id_sub_bab,
                'judul_quiz' => 'Kuis: ' . $request->judul_materi,
            ]);
            $idQuiz = $quiz->id_quiz;

            if ($request->hasFile('file_excel')) {
                $ext = strtolower($request->file('file_excel')->getClientOriginalExtension());
                $rows = \App\Services\ExcelService::parseFile(
                    $request->file('file_excel')->getRealPath(),
                    $ext
                );
                if (!empty($rows)) {
                    array_shift($rows); // skip header
                    foreach ($rows as $r) {
                        if (count($r) >= 6) {
                            [$pertanyaan, $a, $b, $c, $d, $kunci] = array_map('trim', array_slice($r, 0, 6));
                            $kUpper = strtoupper($kunci);
                            if ($pertanyaan && $a && $b && $c && $d && in_array($kUpper, ['A', 'B', 'C', 'D'])) {
                                SoalQuiz::create([
                                    'id_quiz' => $idQuiz,
                                    'pertanyaan' => $pertanyaan,
                                    'opsi_a' => $a,
                                    'opsi_b' => $b,
                                    'opsi_c' => $c,
                                    'opsi_d' => $d,
                                    'kunci_jawaban' => $kUpper,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        Materi::create([
            'id_bab' => $subBab->id_bab,
            'id_sub_bab' => $subBab->id_sub_bab,
            'judul_materi' => trim($request->judul_materi),
            'isi_materi' => $request->isi_materi,
            'tipe_materi' => $request->tipe_materi,
            'url_video' => $request->tipe_materi === 'video' ? $request->url_video : null,
            'file_pdf' => $filePdfPath,
            'id_quiz' => $idQuiz,
        ]);

        return back()->with('success', 'Materi berhasil ditambahkan.');
    }

    public function updateMateri(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);

        $videoValidator = function ($attribute, $value, $fail) use ($request) {
            if ($request->tipe_materi === 'video' && $value) {
                $val = trim($value);
                if (preg_match('#(drive\.google\.com|docs\.google\.com|google\.com/file|google\.com/drive|dropbox\.com|onedrive\.live\.com)#i', $val)) {
                    $fail('Tautan Google Drive atau penyimpanan cloud lainnya dilarang. Harap gunakan tautan YouTube atau file video langsung (.mp4).');
                    return;
                }
                $isYt = (bool) (preg_match('#^(https?:\/\/)?(www\.|m\.)?(youtube(?:-nocookie)?\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtu\.be\/)[a-zA-Z0-9_\-]+#i', $val) || preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts|live)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $val));
                $isMp4 = (bool) preg_match('#^https?:\/\/[^\s]+\.mp4(\?[^\s]*)?$#i', $val);

                if (!$isYt && !$isMp4) {
                    $fail('URL Video pembelajaran HANYA boleh berupa tautan YouTube (youtube.com / youtu.be) atau file video langsung berformat MP4 (.mp4). Link lain seperti Google Drive dilarang.');
                }
            }
        };

        $excelValidator = function ($attribute, $value, $fail) use ($request) {
            if ($request->tipe_materi === 'kuis' && $value) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                    $fail('File kuis wajib berformat spreadsheet Excel / CSV (.xlsx, .xls, atau .csv). Format file lainnya tidak diperbolehkan.');
                }
            }
        };

        $request->validate([
            'judul_materi' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'tipe_materi'  => 'required|in:video,dokumen,kuis',
            'url_video'    => ['nullable', 'required_if:tipe_materi,video', 'max:255', $videoValidator],
            'file_pdf'     => 'nullable|file|mimes:pdf|max:10240',
            'file_excel'   => ['nullable', 'file', 'max:5120', $excelValidator],
        ], [
            'judul_materi.required' => 'Nama materi wajib diisi.',
            'judul_materi.min'      => 'Nama materi minimal 3 karakter.',
            'judul_materi.max'      => 'Nama materi maksimal 100 karakter.',
            'judul_materi.regex'    => 'Nama materi hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol atau karakter khusus).',
            'url_video.required_if' => 'URL Video wajib diisi jika memilih tipe materi Video.',
            'file_pdf.mimes'        => 'File materi harus berformat PDF (.pdf).',
        ]);

        // Guardrail: Anti-duplikasi judul materi di sub-bab yang sama
        $duplicateMateri = Materi::where('id_sub_bab', $materi->id_sub_bab)
            ->where('id_materi', '!=', $id)
            ->whereRaw('LOWER(TRIM(judul_materi)) = ?', [mb_strtolower(trim($request->judul_materi))])
            ->exists();
        if ($duplicateMateri) {
            return back()->withInput()->with('error', 'Materi dengan judul yang sama sudah ada di Sub-Bab ini.');
        }

        $data = [
            'judul_materi' => trim($request->judul_materi),
            'tipe_materi' => $request->tipe_materi,
            'isi_materi' => $request->isi_materi,
        ];

        if ($request->tipe_materi === 'video') {
            $data['url_video'] = $request->url_video;
        } elseif ($request->tipe_materi === 'dokumen') {
            if ($request->hasFile('file_pdf')) {
                if ($materi->file_pdf && Storage::disk('public')->exists($materi->file_pdf)) {
                    Storage::disk('public')->delete($materi->file_pdf);
                }
                $data['file_pdf'] = $request->file('file_pdf')->store('materi_pdf', 'public');
            }
        }

        $materi->update($data);

        return back()->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroyMateri($id)
    {
        $materi = Materi::findOrFail($id);
        if ($materi->file_pdf && Storage::disk('public')->exists($materi->file_pdf)) {
            Storage::disk('public')->delete($materi->file_pdf);
        }
        if ($materi->quiz) {
            $materi->quiz->delete();
        }
        $materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }

    public function destroyBab($id)
    {
        Bab::findOrFail($id)->delete();
        return back()->with('success', 'Bab berhasil dihapus.');
    }

    public function destroySubBab($id)
    {
        SubBab::findOrFail($id)->delete();
        return back()->with('success', 'Sub-Bab berhasil dihapus.');
    }

    public function downloadPdf($id)
    {
        $materi = Materi::findOrFail($id);

        if (empty($materi->file_pdf) || !Storage::disk('public')->exists($materi->file_pdf)) {
            return back()->with('error', 'Berkas PDF materi tidak ditemukan atau belum diunggah.');
        }

        $cleanTitle = Str::slug($materi->judul_materi) ?: 'materi-' . $materi->id_materi;
        $fileName = $cleanTitle . '.pdf';

        return Storage::disk('public')->download(
            $materi->file_pdf,
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }
}
