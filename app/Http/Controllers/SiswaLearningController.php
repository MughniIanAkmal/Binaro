<?php

namespace App\Http\Controllers;

use App\Models\Bab;
use App\Models\HasilKuisSiswa;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Quiz;
use App\Models\SoalQuiz;
use App\Models\SubBab;
use Illuminate\Http\Request;

class SiswaLearningController extends Controller
{
    // 1. Mapel list for Siswa
    public function mapelIndex()
    {
        $mapels = MataPelajaran::withCount(['bab'])->get();
        return view('siswa.mapel.index', compact('mapels'));
    }

    // 2. View Sub-Bab list for selected Mapel
    public function materiIndex(Request $request, $idMapel)
    {
        $mapel = MataPelajaran::findOrFail($idMapel);
        $babs = Bab::with(['subBab.materi'])
                   ->where('id_mapel', $idMapel)
                   ->get();

        return view('siswa.materi.index', compact('mapel', 'babs'));
    }

    // 2.1 View Materi page under selected Sub-Bab
    public function subBabMateri($idSubBab)
    {
        $subBab = SubBab::with(['bab.mataPelajaran', 'materi.quiz'])->findOrFail($idSubBab);
        $siswaId = session('user_id');

        return view('siswa.sub_bab.materi', compact('subBab', 'siswaId'));
    }

    // 3. View individual Materi (Video Player or PDF Viewer)
    public function viewMateri($idMateri)
    {
        $materi = Materi::with(['subBab.bab.mataPelajaran', 'quiz'])->findOrFail($idMateri);
        $siswaId = session('user_id');

        $hasilKuis = null;
        if ($materi->tipe_materi === 'kuis' && $materi->id_quiz) {
            $hasilKuis = HasilKuisSiswa::where('id_quiz', $materi->id_quiz)
                                       ->where('id_siswa', $siswaId)
                                       ->first();
        }

        return view('siswa.materi.view', compact('materi', 'hasilKuis'));
    }

    // 3.5 Daftar Ujian Siswa (Online Exams)
    // 3.5 Daftar Ujian Siswa (Online Exams Resmi - Bukan Kuis Materi)
    public function daftarUjian(Request $request)
    {
        $siswaId = session('user_id');
        $siswa = \App\Models\Siswa::with('kelas')->find($siswaId);

        // Fetch all official exams (where id_sub_bab is NULL)
        $allQuizzes = Quiz::whereNull('id_sub_bab')
            ->with([
                'mataPelajaran',
                'guru',
                'soal',
                'targetSiswa',
                'hasilSiswa' => function($q) use ($siswaId) {
                    if ($siswaId) {
                        $q->where('id_siswa', $siswaId);
                    }
                }
            ])->latest('id_quiz')->get();

        // Filter target siswa jika tipe pilihan
        $accessibleQuizzes = $allQuizzes->filter(function($q) use ($siswaId) {
            if ($q->target_tipe === 'pilihan' && $siswaId) {
                return $q->targetSiswa->contains('id_siswa', $siswaId);
            }
            return true;
        })->values();

        $accessibleQuizzes->each(function($q) {
            $q->mudah_count = $q->soal->where('tingkat_kesulitan', 'mudah')->count();
            $q->sedang_count = $q->soal->filter(fn($s) => ($s->tingkat_kesulitan === 'sedang' || empty($s->tingkat_kesulitan)))->count();
            $q->sulit_count = $q->soal->filter(fn($s) => in_array($s->tingkat_kesulitan, ['sulit', 'susah']))->count();
        });

        $ujianTersedia = $accessibleQuizzes->filter(function($q) {
            return $q->hasilSiswa->isEmpty();
        })->values();

        $ujianSelesai = $accessibleQuizzes->filter(function($q) {
            return $q->hasilSiswa->isNotEmpty();
        })->values();

        // Latest result for the "Nilai Ujian Terakhir" section (hanya ujian resmi yang sudah dirilis/dikirim oleh guru)
        $latestHasil = HasilKuisSiswa::with(['quiz.mataPelajaran'])
            ->whereHas('quiz', function($q) {
                $q->whereNull('id_sub_bab');
            })
            ->where('id_siswa', $siswaId)
            ->where('status_kirim', true)
            ->latest('id_hasil')
            ->first();

        return view('siswa.ujian.index', compact('siswa', 'ujianTersedia', 'ujianSelesai', 'latestHasil'));
    }

    // 3.6 Petunjuk Ujian Resmi (Exam Instructions before Start)
    public function petunjukUjian($idQuiz)
    {
        $quiz = Quiz::with(['mataPelajaran', 'guru', 'subBab.bab.mataPelajaran', 'soal'])->findOrFail($idQuiz);
        $siswaId = session('user_id');
        $siswa = \App\Models\Siswa::with('kelas')->find($siswaId);

        // Jika ini adalah kuis materi, arahkan ke kuis materi play langsung
        if (!empty($quiz->id_sub_bab)) {
            return redirect()->route('siswa.quiz.play', $idQuiz);
        }

        // Check if already taken
        $alreadySubmitted = HasilKuisSiswa::where('id_quiz', $idQuiz)
            ->where('id_siswa', $siswaId)
            ->first();

        if ($alreadySubmitted) {
            return redirect()->route('siswa.ujian.result', $idQuiz);
        }

        $totalSoal = $quiz->soal()->count();
        $countMudah = $quiz->soal->where('tingkat_kesulitan', 'mudah')->count();
        $countSedang = $quiz->soal->filter(fn($s) => ($s->tingkat_kesulitan === 'sedang' || empty($s->tingkat_kesulitan)))->count();
        $countSulit = $quiz->soal->filter(fn($s) => in_array($s->tingkat_kesulitan, ['sulit', 'susah']))->count();

        return view('siswa.ujian.petunjuk', compact('quiz', 'siswa', 'totalSoal', 'countMudah', 'countSedang', 'countSulit'));
    }

    // 4. Play Quiz / Ujian (Menampilkan Soal)
    public function playQuiz(Request $request, $idQuiz)
    {
        $quiz = Quiz::with(['mataPelajaran', 'guru', 'subBab.bab.mataPelajaran', 'materi'])->findOrFail($idQuiz);
        $siswaId = session('user_id');

        // Guardrail: UNIQUE(id_quiz, id_siswa) session lock
        $alreadySubmitted = HasilKuisSiswa::where('id_quiz', $idQuiz)
                                          ->where('id_siswa', $siswaId)
                                          ->first();

        if ($alreadySubmitted) {
            $targetRoute = !empty($quiz->id_sub_bab) ? 'siswa.quiz.result' : (request()->routeIs('siswa.ujian.*') ? 'siswa.ujian.result' : 'siswa.quiz.result');
            $infoMsg = !empty($quiz->id_sub_bab) ? 'Anda telah menyelesaikan kuis materi ini sebelumnya.' : 'Anda telah mengumpulkan lembar ujian ini sebelumnya.';
            return redirect()->route($targetRoute, $idQuiz)->with('info', $infoMsg);
        }

        $pilihanKesulitan = strtolower($request->query('kesulitan', 'semua'));
        if ($pilihanKesulitan === 'susah') $pilihanKesulitan = 'sulit';

        // Ambil butir soal
        // Jika kuis sub-bab materi dan memiliki bank soal > 5, ambil 5 soal sesuai rule PRD kuis sub-bab.
        // Jika ujian resmi CBT:
        if ($quiz->id_sub_bab && SoalQuiz::where('id_quiz', $idQuiz)->count() > 5) {
            $soals = SoalQuiz::where('id_quiz', $idQuiz)->inRandomOrder()->take(5)->get();
        } else {
            $soalQuery = SoalQuiz::where('id_quiz', $idQuiz);
            if (in_array($pilihanKesulitan, ['mudah', 'sedang', 'sulit'])) {
                if ($pilihanKesulitan === 'sulit') {
                    $soalQuery->whereIn('tingkat_kesulitan', ['sulit', 'susah']);
                } else {
                    $soalQuery->where('tingkat_kesulitan', $pilihanKesulitan);
                }
            }

            $filteredSoals = $soalQuery->orderBy('id_soal', 'asc')->get();

            // Jika ada soal yang sesuai dengan tingkat kesulitan yang dipilih, sajikan soal tersebut.
            // Jika kosong, fallback otomatis ke semua soal ujian agar siswa tidak terblokir.
            if ($filteredSoals->isNotEmpty()) {
                $soals = $filteredSoals;
            } else {
                $soals = SoalQuiz::where('id_quiz', $idQuiz)->orderBy('id_soal', 'asc')->get();
                $pilihanKesulitan = 'semua';
            }
        }

        if ($soals->isEmpty()) {
            $errorMsg = !empty($quiz->id_sub_bab)
                ? 'Guru belum menginput butir soal untuk kuis ini.'
                : 'Guru belum menginput butir soal untuk ujian ini. Silakan hubungi guru pengampu.';
            return back()->with('error', $errorMsg);
        }

        // Pisahkan tampilan: Kuis Materi vs Ujian Online Resmi CBT
        if (empty($quiz->id_sub_bab)) {
            return view('siswa.ujian.play', compact('quiz', 'soals', 'pilihanKesulitan'));
        }

        return view('siswa.quiz.play', compact('quiz', 'soals', 'pilihanKesulitan'));
    }

    // 4.1 Play Ujian Online CBT Resmi
    public function playUjian(Request $request, $idQuiz)
    {
        $quiz = Quiz::with(['mataPelajaran', 'guru', 'subBab.bab.mataPelajaran'])->findOrFail($idQuiz);
        if (!empty($quiz->id_sub_bab)) {
            return redirect()->route('siswa.quiz.play', $idQuiz);
        }
        return $this->playQuiz($request, $idQuiz);
    }

    // 5. Submit Quiz & Auto-grading
    public function submitQuiz(Request $request, $idQuiz)
    {
        $quiz = Quiz::findOrFail($idQuiz);
        $siswaId = session('user_id');

        $request->validate([
            'jawaban' => 'nullable|array',
            'jawaban.*' => 'nullable|string|max:5',
        ]);

        $isKuis = !empty($quiz->id_sub_bab);
        $targetResultRoute = $isKuis 
            ? 'siswa.quiz.result' 
            : ($request->routeIs('siswa.ujian.*') ? 'siswa.ujian.result' : 'siswa.quiz.result');

        // Prevent double submit
        $existing = HasilKuisSiswa::where('id_quiz', $idQuiz)
                                  ->where('id_siswa', $siswaId)
                                  ->first();
        if ($existing) {
            return redirect()->route($targetResultRoute, $idQuiz);
        }

        $answers = $request->input('jawaban', []); // array [id_soal => 'A']
        $soalIds = $request->input('soal_ids', []);

        if (!empty($soalIds) && is_array($soalIds)) {
            $allQuizSoals = SoalQuiz::where('id_quiz', $idQuiz)->whereIn('id_soal', $soalIds)->orderBy('id_soal', 'asc')->get();
        } elseif ($quiz->id_sub_bab && count($answers) <= 5 && SoalQuiz::where('id_quiz', $idQuiz)->count() > 5) {
            $allQuizSoals = SoalQuiz::whereIn('id_soal', array_keys($answers))->get();
        } elseif (!empty($answers)) {
            $allQuizSoals = SoalQuiz::where('id_quiz', $idQuiz)->whereIn('id_soal', array_keys($answers))->orderBy('id_soal', 'asc')->get();
        } else {
            $allQuizSoals = SoalQuiz::where('id_quiz', $idQuiz)->orderBy('id_soal', 'asc')->get();
        }

        $totalSoal = max($allQuizSoals->count(), 1);

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $reviewDetails = [];

        foreach ($allQuizSoals as $soal) {
            $userAns = strtoupper($answers[$soal->id_soal] ?? '');
            $kunci = strtoupper($soal->kunci_jawaban);
            $isCorrect = (!empty($userAns) && $userAns === $kunci);

            if ($isCorrect) {
                $jumlahBenar++;
            } else {
                $jumlahSalah++;
            }

            $reviewDetails[] = [
                'id_soal' => $soal->id_soal,
                'pertanyaan' => $soal->pertanyaan,
                'gambar' => $soal->gambar,
                'tingkat_kesulitan' => $soal->tingkat_kesulitan ?? 'sedang',
                'opsi_a' => $soal->opsi_a,
                'opsi_b' => $soal->opsi_b,
                'opsi_c' => $soal->opsi_c,
                'opsi_d' => $soal->opsi_d,
                'user_answer' => $userAns,
                'kunci_jawaban' => $kunci,
                'is_correct' => $isCorrect,
            ];
        }

        $nilaiAkhir = round(($jumlahBenar / $totalSoal) * 100, 2);

        // Nilai ujian online (tanpa id_sub_bab) ditahan sampai guru merilis nilai melalui fitur 'Kirim Nilai ke Semua Siswa'.
        // Untuk kuis materi latihan sub-bab mandiri, nilai langsung ditampilkan agar siswa tahu pemahamannya.
        $statusKirim = $isKuis;

        HasilKuisSiswa::create([
            'id_quiz' => $idQuiz,
            'id_siswa' => $siswaId,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'nilai_akhir' => $nilaiAkhir,
            'status_kirim' => $statusKirim,
            'waktu_kirim' => $statusKirim ? now() : null,
        ]);

        session()->flash('quiz_review_' . $idQuiz, $reviewDetails);

        $pesan = $isKuis
            ? 'Kuis materi berhasil diselesaikan! Berikut ringkasan nilai dan pembahasannya.'
            : 'Lembar jawaban ujian berhasil dikumpulkan.';

        return redirect()->route($targetResultRoute, $idQuiz)->with('success', $pesan);
    }

    // 5.1 Submit Ujian Online CBT Resmi
    public function submitUjian(Request $request, $idQuiz)
    {
        return $this->submitQuiz($request, $idQuiz);
    }

    // 6. View Quiz Result / Review
    public function resultQuiz($idQuiz)
    {
        $quiz = Quiz::with(['mataPelajaran', 'guru', 'subBab.bab.mataPelajaran', 'materi'])->findOrFail($idQuiz);
        $siswaId = session('user_id');

        $hasil = HasilKuisSiswa::where('id_quiz', $idQuiz)
                               ->where('id_siswa', $siswaId)
                               ->firstOrFail();

        $reviewDetails = session('quiz_review_' . $idQuiz, null);
        if (!$reviewDetails) {
            $quizSoals = SoalQuiz::where('id_quiz', $idQuiz)->orderBy('id_soal', 'asc')->get();
            $reviewDetails = $quizSoals->map(function ($s) {
                return [
                    'id_soal' => $s->id_soal,
                    'pertanyaan' => $s->pertanyaan,
                    'gambar' => $s->gambar,
                    'opsi_a' => $s->opsi_a,
                    'opsi_b' => $s->opsi_b,
                    'opsi_c' => $s->opsi_c,
                    'opsi_d' => $s->opsi_d,
                    'user_answer' => null,
                    'kunci_jawaban' => $s->kunci_jawaban,
                    'is_correct' => null,
                ];
            })->toArray();
        }

        // Pisahkan tampilan: Kuis Materi vs Ujian Online Resmi CBT
        if (empty($quiz->id_sub_bab)) {
            return view('siswa.ujian.result', compact('quiz', 'hasil', 'reviewDetails'));
        }

        return view('siswa.quiz.result', compact('quiz', 'hasil', 'reviewDetails'));
    }

    // 6.1 View Ujian Online Result
    public function resultUjian($idQuiz)
    {
        $quiz = Quiz::with(['mataPelajaran', 'guru', 'subBab.bab.mataPelajaran'])->findOrFail($idQuiz);
        if (!empty($quiz->id_sub_bab)) {
            return redirect()->route('siswa.quiz.result', $idQuiz);
        }
        return $this->resultQuiz($idQuiz);
    }

    // 7. Jadwal Mapel Siswa (read-only, tersambung ke jadwal yang dibuat admin)
    public function jadwalIndex()
    {
        $siswaId = session('user_id');
        $siswa   = \App\Models\Siswa::with('kelas')->find($siswaId);

        // Query jadwal berdasarkan kelas siswa
        $query = \App\Models\JadwalMataPelajaran::with(['mataPelajaran', 'guru', 'kelas']);
        if ($siswa && $siswa->id_rooms) {
            $query->where('id_rooms', $siswa->id_rooms);
        }

        $jadwals = $query
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->orderBy('jam')
            ->get();

        // Fallback: tampilkan semua jadwal jika kelas siswa kosong
        if ($jadwals->isEmpty()) {
            $jadwals = \App\Models\JadwalMataPelajaran::with(['mataPelajaran', 'guru', 'kelas'])
                ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
                ->orderBy('jam')
                ->get();
        }

        // Kelompokkan per hari
        $jadwalPerHari = [
            'Senin'  => $jadwals->where('hari', 'Senin')->values(),
            'Selasa' => $jadwals->where('hari', 'Selasa')->values(),
            'Rabu'   => $jadwals->where('hari', 'Rabu')->values(),
            'Kamis'  => $jadwals->where('hari', 'Kamis')->values(),
            'Jumat'  => $jadwals->where('hari', 'Jumat')->values(),
            'Sabtu'  => $jadwals->where('hari', 'Sabtu')->values(),
        ];

        $hariMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $hariIni    = $hariMap[date('l')] ?? 'Senin';
        $totalSesi  = $jadwals->count();
        $totalMapel = $jadwals->pluck('id_mapel')->unique()->count();
        $totalGuru  = $jadwals->pluck('id_guru')->unique()->count();

        return view('siswa.jadwal-mapel.index', compact(
            'siswa', 'jadwals', 'jadwalPerHari', 'hariIni',
            'totalSesi', 'totalMapel', 'totalGuru'
        ));
    }
}
