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
        $guru = $this->authenticatedGuru();
        $search = $request->get('search');
        if ($search) {
            $search = preg_replace('/[^a-zA-Z0-9\s]/u', '', (string)$search);
        }
        $level = $request->get('level');
        $mapelId = $request->get('mapel_id');

    $query = Quiz::whereNull('id_sub_bab')
        ->where('id_guru', $guru->id_guru)
            ->with(['mataPelajaran', 'guru', 'soal', 'targetSiswa', 'hasilSiswa'])
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

        // Statistik singkat (khusus ujian online resmi)
        $totalUjian = Quiz::whereNull('id_sub_bab')
            ->where('id_guru', $guru->id_guru)
            ->count();
        $totalSoal = SoalQuiz::whereHas('quiz', function ($q) use ($guru) {
            $q->whereNull('id_sub_bab')
            ->where('id_guru', $guru->id_guru);
        })->count();
        $totalHasil = DB::table('hasil_kuis_siswa')
            ->join('quiz', 'hasil_kuis_siswa.id_quiz', '=', 'quiz.id_quiz')
            ->whereNull('quiz.id_sub_bab')
            ->where('quiz.id_guru', $guru->id_guru)
            ->count();

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
        $guru = $this->authenticatedGuru();
        $request->validate([
            'judul_quiz'    => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/u',
            ],
            'id_mapel'      => 'required|exists:mata_pelajaran,id_mapel',
            'tingkat_level' => 'required|in:mudah,sedang,susah',
            'durasi_menit'  => 'required|integer|min:5|max:300',
            'target_tipe'   => 'required|in:semua,pilihan',
            'target_siswa'  => 'nullable|array',
            'target_siswa.*'=> 'exists:siswa,id_siswa',
            'deskripsi'     => 'nullable|string|max:1000',
            'soal'          => 'nullable|array',
            'soal.*.pertanyaan' => 'nullable|string|max:2000',
            'soal.*.opsi_a' => 'nullable|string|max:255',
            'soal.*.opsi_b' => 'nullable|string|max:255',
            'soal.*.opsi_c' => 'nullable|string|max:255',
            'soal.*.opsi_d' => 'nullable|string|max:255',
            'soal.*.kunci_jawaban' => 'nullable|in:A,B,C,D',
            'soal.*.bobot_nilai' => 'nullable|numeric|min:1|max:100',
            'soal.*.tingkat_kesulitan' => 'nullable|in:mudah,sedang,susah,sulit',
            'soal.*.gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ], [
            'judul_quiz.required'    => 'Judul ujian wajib diisi.',
            'judul_quiz.min'         => 'Judul ujian minimal 3 karakter.',
            'judul_quiz.max'         => 'Judul ujian maksimal 100 karakter.',
            'judul_quiz.regex'       => 'Judul ujian hanya boleh berisi huruf dan spasi (tidak boleh mengandung angka maupun simbol).',
            'id_mapel.required'      => 'Mata pelajaran wajib dipilih.',
            'tingkat_level.required' => 'Tingkatan level ujian wajib dipilih (mudah, sedang, atau susah).',
            'durasi_menit.required'  => 'Durasi ujian wajib diisi (minimal 5 menit).',
            'target_tipe.required'   => 'Pilih sasaran peserta ujian (Semua Siswa atau Siswa Tertentu).',
            'deskripsi.max'          => 'Deskripsi ujian maksimal 1000 karakter.',
        ]);

        if ($request->target_tipe === 'pilihan' && empty($request->target_siswa)) {
            return back()->withInput()->withErrors([
                'target_siswa' => 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.'
            ])->with('error', 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.');
        }

        $judulQuiz = trim($request->judul_quiz);
    $deskripsi = trim((string) $request->deskripsi);

        $duplicateQuiz = Quiz::whereNull('id_sub_bab')
            ->where(function ($q) use ($judulQuiz, $deskripsi) {
                $q->whereRaw('LOWER(TRIM(judul_quiz)) = ?', [mb_strtolower($judulQuiz)])
                  ->orWhereRaw('LOWER(TRIM(nama_quiz)) = ?', [mb_strtolower($judulQuiz)]);

                if (!empty($deskripsi)) {
                    $q->orWhereRaw('LOWER(TRIM(deskripsi)) = ?', [mb_strtolower($deskripsi)]);
                }
            })
            ->first();

        if ($duplicateQuiz) {
            $isDeskripsiSama = !empty($deskripsi) && mb_strtolower(trim($duplicateQuiz->deskripsi)) === mb_strtolower($deskripsi);

            $errorMsg = 'Ujian online dengan judul yang serupa sudah ada (' . $duplicateQuiz->judul_quiz . '). Silakan gunakan judul ujian lain.';
            if ($isDeskripsiSama && mb_strtolower(trim($duplicateQuiz->judul_quiz)) !== mb_strtolower($judulQuiz) && mb_strtolower(trim($duplicateQuiz->nama_quiz)) !== mb_strtolower($judulQuiz)) {
                $errorMsg = 'Ujian online dengan deskripsi yang serupa sudah ada pada ujian (' . $duplicateQuiz->judul_quiz . '). Silakan gunakan deskripsi yang berbeda.';
            } elseif ($isDeskripsiSama) {
                $errorMsg = 'Ujian online dengan judul dan deskripsi yang serupa sudah ada (' . $duplicateQuiz->judul_quiz . '). Silakan gunakan yang lain.';
            }

            return back()->withInput()->withErrors([
                'judul_quiz' => $errorMsg,
                'deskripsi' => $errorMsg
            ]);
        }

        DB::beginTransaction();
        try {
            $quiz = Quiz::create([
                'id_guru'       => $guru->id_guru,
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

                    $kesulitanItem = strtolower($item['tingkat_kesulitan'] ?? '');
                    if (!in_array($kesulitanItem, ['mudah', 'sedang', 'susah', 'sulit'])) {
                        $kesulitanItem = $request->tingkat_level ?? 'sedang';
                    }
                    if ($kesulitanItem === 'susah') $kesulitanItem = 'sulit';

                    SoalQuiz::create([
                        'id_quiz'           => $quiz->id_quiz,
                        'pertanyaan'        => trim($item['pertanyaan']),
                        'gambar'            => $gambarPath,
                        'opsi_a'            => trim($item['opsi_a']),
                        'opsi_b'            => trim($item['opsi_b']),
                        'opsi_c'            => trim($item['opsi_c']),
                        'opsi_d'            => trim($item['opsi_d']),
                        'kunci_jawaban'     => strtoupper($item['kunci_jawaban'] ?? 'A'),
                        'bobot_nilai'       => isset($item['bobot_nilai']) && is_numeric($item['bobot_nilai']) ? (int)$item['bobot_nilai'] : 10,
                        'tingkat_kesulitan' => $kesulitanItem,
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
        $quiz = $this->ownedQuiz($id, [
            'mataPelajaran',
            'guru',
            'soal',
            'targetSiswa.kelas',
            'hasilSiswa.siswa.kelas'
        ]);

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
        $quiz = $this->ownedQuiz($id, ['targetSiswa']);
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
        $quiz = $this->ownedQuiz($id);

        $request->validate([
            'judul_quiz'    => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/u',
            ],
            'id_mapel'      => 'required|exists:mata_pelajaran,id_mapel',
            'tingkat_level' => 'required|in:mudah,sedang,susah',
            'durasi_menit'  => 'required|integer|min:5|max:300',
            'target_tipe'   => 'required|in:semua,pilihan',
            'target_siswa'  => 'nullable|array',
            'target_siswa.*'=> 'exists:siswa,id_siswa',
            'deskripsi'     => 'nullable|string|max:1000',
        ], [
            'judul_quiz.required'    => 'Judul ujian wajib diisi.',
            'judul_quiz.min'         => 'Judul ujian minimal 3 karakter.',
            'judul_quiz.max'         => 'Judul ujian maksimal 100 karakter.',
            'judul_quiz.regex'       => 'Judul ujian hanya boleh berisi huruf dan spasi (tidak boleh mengandung angka maupun simbol).',
            'id_mapel.required'      => 'Mata pelajaran wajib dipilih.',
            'tingkat_level.required' => 'Tingkatan level ujian wajib dipilih.',
            'durasi_menit.required'  => 'Durasi ujian wajib diisi.',
            'deskripsi.max'          => 'Deskripsi ujian maksimal 1000 karakter.',
        ]);

        if ($request->target_tipe === 'pilihan' && empty($request->target_siswa)) {
            return back()->withInput()->withErrors([
                'target_siswa' => 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.'
            ])->with('error', 'Harap pilih minimal satu siswa jika memilih target siswa tertentu.');
        }

        $judulQuiz = trim($request->judul_quiz);
        $deskripsi = trim((string) $request->deskripsi);

        $duplicateQuiz = Quiz::whereNull('id_sub_bab')
            ->where('id_quiz', '!=', $quiz->id_quiz)
            ->where(function ($q) use ($judulQuiz, $deskripsi) {
                $q->whereRaw('LOWER(TRIM(judul_quiz)) = ?', [mb_strtolower($judulQuiz)])
                  ->orWhereRaw('LOWER(TRIM(nama_quiz)) = ?', [mb_strtolower($judulQuiz)]);

                if (!empty($deskripsi)) {
                    $q->orWhereRaw('LOWER(TRIM(deskripsi)) = ?', [mb_strtolower($deskripsi)]);
                }
            })
            ->first();

        if ($duplicateQuiz) {
            $isDeskripsiSama = !empty($deskripsi) && mb_strtolower(trim($duplicateQuiz->deskripsi)) === mb_strtolower($deskripsi);

            $errorMsg = 'Ujian online dengan judul yang serupa sudah ada (' . $duplicateQuiz->judul_quiz . '). Silakan gunakan judul ujian lain.';
            if ($isDeskripsiSama && mb_strtolower(trim($duplicateQuiz->judul_quiz)) !== mb_strtolower($judulQuiz) && mb_strtolower(trim($duplicateQuiz->nama_quiz)) !== mb_strtolower($judulQuiz)) {
                $errorMsg = 'Ujian online dengan deskripsi yang serupa sudah ada pada ujian (' . $duplicateQuiz->judul_quiz . '). Silakan gunakan deskripsi yang berbeda.';
            } elseif ($isDeskripsiSama) {
                $errorMsg = 'Ujian online dengan judul dan deskripsi yang serupa sudah ada (' . $duplicateQuiz->judul_quiz . '). Silakan gunakan yang lain.';
            }

            return back()->withInput()->withErrors([
                'judul_quiz' => $errorMsg,
                'deskripsi'  => $errorMsg,
            ]);
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

            return redirect()->route('guru.ujian.show', $quiz->id_quiz)
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
        $quiz = $this->ownedQuiz($id, ['soal']);

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
        $quiz = $this->ownedQuiz($idQuiz);

        $request->validate([
            'pertanyaan'    => 'required|string|max:2000',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'opsi_a'        => 'required|string|max:255',
            'opsi_b'        => 'required|string|max:255',
            'opsi_c'        => 'required|string|max:255',
            'opsi_d'        => 'required|string|max:255',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'bobot_nilai'   => 'required|numeric|min:1|max:100',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,susah,sulit',
        ], [
            'pertanyaan.required'    => 'Teks pertanyaan soal wajib diisi.',
            'pertanyaan.max'         => 'Teks pertanyaan soal maksimal 2000 karakter.',
            'gambar.image'           => 'File pendukung harus berupa gambar valid (JPEG, PNG, WEBP, GIF).',
            'gambar.max'             => 'Ukuran gambar maksimal 2MB.',
            'opsi_a.required'        => 'Pilihan A wajib diisi.',
            'opsi_a.max'             => 'Pilihan A maksimal 255 karakter.',
            'opsi_b.required'        => 'Pilihan B wajib diisi.',
            'opsi_b.max'             => 'Pilihan B maksimal 255 karakter.',
            'opsi_c.required'        => 'Pilihan C wajib diisi.',
            'opsi_c.max'             => 'Pilihan C maksimal 255 karakter.',
            'opsi_d.required'        => 'Pilihan D wajib diisi.',
            'opsi_d.max'             => 'Pilihan D maksimal 255 karakter.',
            'kunci_jawaban.required' => 'Kunci jawaban wajib dipilih.',
            'bobot_nilai.required'   => 'Bobot nilai soal wajib ditentukan.',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('soal_ujian', 'public');
        }

        $kesulitanSoal = strtolower($request->input('tingkat_kesulitan', $quiz->tingkat_level ?? 'sedang'));
        if ($kesulitanSoal === 'susah') $kesulitanSoal = 'sulit';
        if (!in_array($kesulitanSoal, ['mudah', 'sedang', 'sulit'])) $kesulitanSoal = 'sedang';

        SoalQuiz::create([
            'id_quiz'           => $quiz->id_quiz,
            'pertanyaan'        => trim($request->pertanyaan),
            'gambar'            => $gambarPath,
            'opsi_a'            => trim($request->opsi_a),
            'opsi_b'            => trim($request->opsi_b),
            'opsi_c'            => trim($request->opsi_c),
            'opsi_d'            => trim($request->opsi_d),
            'kunci_jawaban'     => strtoupper($request->kunci_jawaban),
            'bobot_nilai'       => $request->bobot_nilai,
            'tingkat_kesulitan' => $kesulitanSoal,
        ]);

        return redirect()->route('guru.ujian.show', $quiz->id_quiz)
            ->with('success', 'Soal ujian baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui soal ujian
     */
    public function updateSoal(Request $request, $idQuiz, $idSoal)
    {
        $this->ownedQuiz($idQuiz);
        $soal = SoalQuiz::where('id_quiz', $idQuiz)->findOrFail($idSoal);

        $request->validate([
            'pertanyaan'    => 'required|string|max:2000',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'opsi_a'        => 'required|string|max:255',
            'opsi_b'        => 'required|string|max:255',
            'opsi_c'        => 'required|string|max:255',
            'opsi_d'        => 'required|string|max:255',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'bobot_nilai'   => 'required|numeric|min:1|max:100',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,susah,sulit',
        ], [
            'pertanyaan.required'    => 'Teks pertanyaan soal wajib diisi.',
            'pertanyaan.max'         => 'Teks pertanyaan soal maksimal 2000 karakter.',
            'gambar.image'           => 'File pendukung harus berupa gambar valid (JPEG, PNG, WEBP, GIF).',
            'gambar.max'             => 'Ukuran gambar maksimal 2MB.',
            'opsi_a.required'        => 'Pilihan A wajib diisi.',
            'opsi_a.max'             => 'Pilihan A maksimal 255 karakter.',
            'opsi_b.required'        => 'Pilihan B wajib diisi.',
            'opsi_b.max'             => 'Pilihan B maksimal 255 karakter.',
            'opsi_c.required'        => 'Pilihan C wajib diisi.',
            'opsi_c.max'             => 'Pilihan C maksimal 255 karakter.',
            'opsi_d.required'        => 'Pilihan D wajib diisi.',
            'opsi_d.max'             => 'Pilihan D maksimal 255 karakter.',
            'kunci_jawaban.required' => 'Kunci jawaban wajib dipilih.',
            'bobot_nilai.required'   => 'Bobot nilai soal wajib ditentukan.',
        ]);

        $kesulitanSoal = strtolower($request->input('tingkat_kesulitan', $soal->tingkat_kesulitan ?? 'sedang'));
        if ($kesulitanSoal === 'susah') $kesulitanSoal = 'sulit';
        if (!in_array($kesulitanSoal, ['mudah', 'sedang', 'sulit'])) $kesulitanSoal = 'sedang';

        $data = [
            'pertanyaan'        => trim($request->pertanyaan),
            'opsi_a'            => trim($request->opsi_a),
            'opsi_b'            => trim($request->opsi_b),
            'opsi_c'            => trim($request->opsi_c),
            'opsi_d'            => trim($request->opsi_d),
            'kunci_jawaban'     => strtoupper($request->kunci_jawaban),
            'bobot_nilai'       => $request->bobot_nilai,
            'tingkat_kesulitan' => $kesulitanSoal,
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
        $this->ownedQuiz($idQuiz);
        $soal = SoalQuiz::where('id_quiz', $idQuiz)->findOrFail($idSoal);

        if ($soal->gambar && Storage::disk('public')->exists($soal->gambar)) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return redirect()->route('guru.ujian.show', $idQuiz)
            ->with('success', 'Soal ujian berhasil dihapus.');
    }

    private function authenticatedGuru(): Guru
    {
        return Guru::findOrFail(session('user_id'));
    }

    private function ownedQuiz($id, array $relations = []): Quiz
    {
        return Quiz::where('id_guru', $this->authenticatedGuru()->id_guru)
            ->with($relations)
            ->findOrFail($id);
    }
}
