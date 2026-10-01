<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UjianController extends Controller
{
    /**
     * Menampilkan daftar semua ujian online
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $level = $request->get('level');
        $mapelId = $request->get('mapel_id');

        $query = Quiz::with(['mataPelajaran', 'guru', 'soal', 'targetSiswa', 'hasilSiswa'])
            ->withCount(['soal', 'hasilSiswa']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_quiz', 'like', "%{$search}%")
                  ->orWhere('nama_quiz', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($level && in_array($level, ['mudah', 'sedang', 'susah'])) {
            $query->where('tingkat_level', $level);
        }

        if ($mapelId) {
            $query->where('id_mapel', $mapelId);
        }

        $ujians = $query->latest('id_quiz')->paginate(10)->withQueryString();
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();

        // Statistik singkat
        $totalUjian = Quiz::count();
        $totalSoal = SoalQuiz::count();
        $totalHasil = DB::table('hasil_kuis_siswa')->count();

        return view('guru.ujian.index', compact('ujians', 'mapels', 'search', 'level', 'mapelId', 'totalUjian', 'totalSoal', 'totalHasil'));
    }

    /**
     * Menampilkan form pembuatan ujian baru
     */
    public function create()
    {
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        $kelasList = Kelas::with(['siswas' => function ($q) {
            $q->orderBy('nm_siswa');
        }])->orderBy('pararel')->get();

        $allSiswas = Siswa::with('kelas')->orderBy('nm_siswa')->get();

        return view('guru.ujian.create', compact('mapels', 'kelasList', 'allSiswas'));
    }

    /**
     * Menyimpan ujian online baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_quiz'    => 'required|string|max:150',
            'id_mapel'      => 'required|exists:mata_pelajaran,id_mapel',
            'tingkat_level' => 'required|in:mudah,sedang,susah',
            'durasi_menit'  => 'required|integer|min:5|max:300',
            'target_tipe'   => 'required|in:semua,pilihan',
            'target_siswa'  => 'nullable|array',
            'target_siswa.*'=> 'exists:siswa,id_siswa',
            'deskripsi'     => 'nullable|string',
        ], [
            'judul_quiz.required'    => 'Judul ujian wajib diisi.',
            'id_mapel.required'      => 'Mata pelajaran wajib dipilih.',
            'tingkat_level.required' => 'Tingkatan level ujian wajib dipilih (mudah, sedang, atau susah).',
            'durasi_menit.required'  => 'Durasi ujian wajib diisi (minimal 5 menit).',
            'target_tipe.required'   => 'Pilih sasaran peserta ujian (Semua Siswa atau Siswa Tertentu).',
        ]);

        if ($request->target_tipe === 'pilihan' && empty($request->target_siswa)) {
            return back()->withInput()->withErrors([
                'target_siswa' => 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.'
            ])->with('error', 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.');
        }

        $guruId = session('user_id');
        $guru = $guruId ? Guru::find($guruId) : Guru::first();
        if (!$guru) {
            $guru = Guru::first();
        }
        $guruId = $guru?->id_guru;

        DB::beginTransaction();
        try {
            $quiz = Quiz::create([
                'id_guru'       => $guruId,
                'id_mapel'      => $request->id_mapel,
                'judul_quiz'    => trim($request->judul_quiz),
                'nama_quiz'     => trim($request->judul_quiz),
                'tingkat_level' => $request->tingkat_level,
                'durasi_menit'  => $request->durasi_menit,
                'target_tipe'   => $request->target_tipe,
                'deskripsi'     => $request->deskripsi,
            ]);

            // Sync target siswa jika tipe pilihan
            if ($request->target_tipe === 'pilihan' && is_array($request->target_siswa)) {
                $quiz->targetSiswa()->sync($request->target_siswa);
            }

            // Simpan soal awal jika ada diinput langsung dari form create
            if ($request->has('soal') && is_array($request->soal)) {
                foreach ($request->soal as $index => $item) {
                    if (empty($item['pertanyaan']) || empty($item['opsi_a']) || empty($item['opsi_b']) || empty($item['opsi_c']) || empty($item['opsi_d'])) {
                        continue;
                    }

                    $gambarPath = null;
                    if ($request->hasFile("soal.{$index}.gambar")) {
                        $gambarFile = $request->file("soal.{$index}.gambar");
                        $gambarPath = $gambarFile->store('soal_ujian', 'public');
                    }

                    SoalQuiz::create([
                        'id_quiz'       => $quiz->id_quiz,
                        'pertanyaan'    => trim($item['pertanyaan']),
                        'gambar'        => $gambarPath,
                        'opsi_a'        => trim($item['opsi_a']),
                        'opsi_b'        => trim($item['opsi_b']),
                        'opsi_c'        => trim($item['opsi_c']),
                        'opsi_d'        => trim($item['opsi_d']),
                        'kunci_jawaban' => strtoupper($item['kunci_jawaban'] ?? 'A'),
                        'bobot_nilai'   => isset($item['bobot_nilai']) && is_numeric($item['bobot_nilai']) ? (int)$item['bobot_nilai'] : 10,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('guru.ujian.index')
                ->with('success', 'Berhasil menambah ujian online.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan ujian: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail ujian online beserta daftar soal dan peserta
     */
    public function show($id)
    {
        $quiz = Quiz::with([
            'mataPelajaran',
            'guru',
            'soal',
            'targetSiswa.kelas',
            'hasilSiswa.siswa.kelas'
        ])->findOrFail($id);

        $totalBobot = $quiz->soal->sum('bobot_nilai');
        $kelasList = Kelas::with(['siswas' => function ($q) {
            $q->orderBy('nm_siswa');
        }])->get();

        return view('guru.ujian.show', compact('quiz', 'totalBobot', 'kelasList'));
    }

    /**
     * Form edit ujian online
     */
    public function edit($id)
    {
        $quiz = Quiz::with('targetSiswa')->findOrFail($id);
        $mapels = MataPelajaran::orderBy('nama_mapel')->get();
        $kelasList = Kelas::with(['siswas' => function ($q) {
            $q->orderBy('nm_siswa');
        }])->orderBy('pararel')->get();
        $allSiswas = Siswa::with('kelas')->orderBy('nm_siswa')->get();

        $selectedSiswaIds = $quiz->targetSiswa->pluck('id_siswa')->toArray();

        return view('guru.ujian.edit', compact('quiz', 'mapels', 'kelasList', 'allSiswas', 'selectedSiswaIds'));
    }

    /**
     * Update ujian online
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $request->validate([
            'judul_quiz'    => 'required|string|max:150',
            'id_mapel'      => 'required|exists:mata_pelajaran,id_mapel',
            'tingkat_level' => 'required|in:mudah,sedang,susah',
            'durasi_menit'  => 'required|integer|min:5|max:300',
            'target_tipe'   => 'required|in:semua,pilihan',
            'target_siswa'  => 'nullable|array',
            'target_siswa.*'=> 'exists:siswa,id_siswa',
            'deskripsi'     => 'nullable|string',
        ], [
            'judul_quiz.required'    => 'Judul ujian wajib diisi.',
            'id_mapel.required'      => 'Mata pelajaran wajib dipilih.',
            'tingkat_level.required' => 'Tingkatan level ujian wajib dipilih.',
            'durasi_menit.required'  => 'Durasi ujian wajib diisi.',
        ]);

        if ($request->target_tipe === 'pilihan' && empty($request->target_siswa)) {
            return back()->withInput()->withErrors([
                'target_siswa' => 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.'
            ])->with('error', 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.');
        }

        DB::beginTransaction();
        try {
            $quiz->update([
                'id_mapel'      => $request->id_mapel,
                'judul_quiz'    => trim($request->judul_quiz),
                'nama_quiz'     => trim($request->judul_quiz),
                'tingkat_level' => $request->tingkat_level,
                'durasi_menit'  => $request->durasi_menit,
                'target_tipe'   => $request->target_tipe,
                'deskripsi'     => $request->deskripsi,
            ]);

            if ($request->target_tipe === 'pilihan') {
                $quiz->targetSiswa()->sync($request->target_siswa ?? []);
            } else {
                $quiz->targetSiswa()->detach();
            }

            DB::commit();

            return redirect()->route('guru.ujian.index')
                ->with('success', 'Data ujian online berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui ujian: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus ujian online beserta relasinya
     */
    public function destroy($id)
    {
        $quiz = Quiz::with('soal')->findOrFail($id);

        try {
            // Hapus file gambar soal di storage jika ada
            foreach ($quiz->soal as $soal) {
                if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                    Storage::disk('public')->delete($soal->gambar);
                }
            }

            $quiz->delete();

            return redirect()->route('guru.ujian.index')
                ->with('success', 'Ujian online berhasil dihapus beserta seluruh soal dan data terkait.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus ujian: ' . $e->getMessage());
        }
    }

    /**
     * Menambah soal ujian baru
     */
    public function storeSoal(Request $request, $idQuiz)
    {
        $quiz = Quiz::findOrFail($idQuiz);

        $request->validate([
            'pertanyaan'    => 'required|string',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'opsi_a'        => 'required|string|max:255',
            'opsi_b'        => 'required|string|max:255',
            'opsi_c'        => 'required|string|max:255',
            'opsi_d'        => 'required|string|max:255',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'bobot_nilai'   => 'required|numeric|min:1|max:100',
        ], [
            'pertanyaan.required'    => 'Teks pertanyaan soal wajib diisi.',
            'gambar.image'           => 'File pendukung harus berupa gambar valid (JPEG, PNG, WEBP, GIF).',
            'gambar.max'             => 'Ukuran gambar maksimal 2MB.',
            'opsi_a.required'        => 'Pilihan A wajib diisi.',
            'opsi_b.required'        => 'Pilihan B wajib diisi.',
            'opsi_c.required'        => 'Pilihan C wajib diisi.',
            'opsi_d.required'        => 'Pilihan D wajib diisi.',
            'kunci_jawaban.required' => 'Kunci jawaban wajib dipilih.',
            'bobot_nilai.required'   => 'Bobot nilai soal wajib ditentukan.',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soal_ujian', 'public');
        }

        SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => trim($request->pertanyaan),
            'gambar'        => $gambarPath,
            'opsi_a'        => trim($request->opsi_a),
            'opsi_b'        => trim($request->opsi_b),
            'opsi_c'        => trim($request->opsi_c),
            'opsi_d'        => trim($request->opsi_d),
            'kunci_jawaban' => strtoupper($request->kunci_jawaban),
            'bobot_nilai'   => $request->bobot_nilai,
        ]);

        return redirect()->route('guru.ujian.show', $quiz->id_quiz)
            ->with('success', 'Soal ujian baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui soal ujian
     */
    public function updateSoal(Request $request, $idQuiz, $idSoal)
    {
        $soal = SoalQuiz::where('id_quiz', $idQuiz)->findOrFail($idSoal);

        $request->validate([
            'pertanyaan'    => 'required|string',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'opsi_a'        => 'required|string|max:255',
            'opsi_b'        => 'required|string|max:255',
            'opsi_c'        => 'required|string|max:255',
            'opsi_d'        => 'required|string|max:255',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'bobot_nilai'   => 'required|numeric|min:1|max:100',
        ]);

        $data = [
            'pertanyaan'    => trim($request->pertanyaan),
            'opsi_a'        => trim($request->opsi_a),
            'opsi_b'        => trim($request->opsi_b),
            'opsi_c'        => trim($request->opsi_c),
            'opsi_d'        => trim($request->opsi_d),
            'kunci_jawaban' => strtoupper($request->kunci_jawaban),
            'bobot_nilai'   => $request->bobot_nilai,
        ];

        // Jika minta hapus gambar lama
        if ($request->boolean('hapus_gambar') && $soal->gambar) {
            if (Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $data['gambar'] = null;
        }

        // Jika upload gambar baru
        if ($request->hasFile('gambar')) {
            if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('soal_ujian', 'public');
        }

        $soal->update($data);

        return redirect()->route('guru.ujian.show', $idQuiz)
            ->with('success', 'Soal ujian berhasil diperbarui.');
    }

    /**
     * Menghapus satu soal ujian
     */
    public function destroySoal($idQuiz, $idSoal)
    {
        $soal = SoalQuiz::where('id_quiz', $idQuiz)->findOrFail($idSoal);

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return redirect()->route('guru.ujian.show', $idQuiz)
            ->with('success', 'Soal ujian berhasil dihapus.');
    }
}
