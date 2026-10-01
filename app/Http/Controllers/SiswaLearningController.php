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
    public function daftarUjian(Request $request)
    {
        $siswaId = session('user_id');
        $siswa = \App\Models\Siswa::with('kelas')->find($siswaId);

        // Fetch all quizzes with their mapel and subBab
        $allQuizzes = Quiz::with(['subBab.bab.mataPelajaran', 'hasilSiswa' => function($q) use ($siswaId) {
            $q->where('id_siswa', $siswaId);
        }])->get();

        $ujianTersedia = $allQuizzes->filter(function($q) {
            return $q->hasilSiswa->isEmpty();
        })->values();

        $ujianSelesai = $allQuizzes->filter(function($q) {
            return $q->hasilSiswa->isNotEmpty();
        })->values();

        // Latest result for the "Nilai Ujian Terakhir" section
        $latestHasil = HasilKuisSiswa::with(['quiz.subBab.bab.mataPelajaran'])
            ->where('id_siswa', $siswaId)
            ->latest()
            ->first();

        return view('siswa.ujian.index', compact('siswa', 'ujianTersedia', 'ujianSelesai', 'latestHasil'));
    }

    // 3.6 Petunjuk Ujian (Exam Instructions before Start)
    public function petunjukUjian($idQuiz)
    {
        $quiz = Quiz::with(['subBab.bab.mataPelajaran', 'soal'])->findOrFail($idQuiz);
        $siswaId = session('user_id');
        $siswa = \App\Models\Siswa::with('kelas')->find($siswaId);

        // Check if already taken
        $alreadySubmitted = HasilKuisSiswa::where('id_quiz', $idQuiz)
            ->where('id_siswa', $siswaId)
            ->first();

        if ($alreadySubmitted) {
            return redirect()->route('siswa.quiz.result', $idQuiz);
        }

        $totalSoal = $quiz->soal()->count() ?: 20;

        return view('siswa.ujian.petunjuk', compact('quiz', 'siswa', 'totalSoal'));
    }

    // 4. Play Quiz (Random 5 Soal)
    public function playQuiz($idQuiz)
    {
        $quiz = Quiz::with('subBab.bab.mataPelajaran')->findOrFail($idQuiz);
        $siswaId = session('user_id');

        // Guardrail: UNIQUE(id_quiz, id_siswa) session lock
        $alreadySubmitted = HasilKuisSiswa::where('id_quiz', $idQuiz)
                                          ->where('id_siswa', $siswaId)
                                          ->first();

        if ($alreadySubmitted) {
            return redirect()->route('siswa.quiz.result', $idQuiz)
                             ->with('info', 'Anda telah menyelesaikan kuis ini sebelumnya.');
        }

        // Randomize 5 questions from question bank
        $soals = SoalQuiz::where('id_quiz', $idQuiz)
                         ->inRandomOrder()
                         ->take(5)
                         ->get();

        if ($soals->isEmpty()) {
            return back()->with('error', 'Bank soal untuk kuis ini belum tersedia.');
        }

        return view('siswa.quiz.play', compact('quiz', 'soals'));
    }

    // 5. Submit Quiz & Auto-grading
    public function submitQuiz(Request $request, $idQuiz)
    {
        $quiz = Quiz::findOrFail($idQuiz);
        $siswaId = session('user_id');

        // Prevent double submit
        $existing = HasilKuisSiswa::where('id_quiz', $idQuiz)
                                  ->where('id_siswa', $siswaId)
                                  ->first();
        if ($existing) {
            return redirect()->route('siswa.quiz.result', $idQuiz);
        }

        $answers = $request->input('jawaban', []); // array [id_soal => 'A']
        $soalIds = array_keys($answers);

        $soals = SoalQuiz::whereIn('id_soal', $soalIds)->get();

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalSoal = max(count($soals), 1);
        $reviewDetails = [];

        foreach ($soals as $soal) {
            $userAns = strtoupper($answers[$soal->id_soal] ?? '');
            $kunci = strtoupper($soal->kunci_jawaban);
            $isCorrect = ($userAns === $kunci);

            if ($isCorrect) {
                $jumlahBenar++;
            } else {
                $jumlahSalah++;
            }

            $reviewDetails[] = [
                'id_soal' => $soal->id_soal,
                'pertanyaan' => $soal->pertanyaan,
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

        HasilKuisSiswa::create([
            'id_quiz' => $idQuiz,
            'id_siswa' => $siswaId,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'nilai_akhir' => $nilaiAkhir,
        ]);

        session()->flash('quiz_review_' . $idQuiz, $reviewDetails);

        return redirect()->route('siswa.quiz.result', $idQuiz)
                         ->with('success', 'Kuis berhasil dikirim.');
    }

    // 6. View Quiz Result / Review
    public function resultQuiz($idQuiz)
    {
        $quiz = Quiz::with('subBab.bab.mataPelajaran')->findOrFail($idQuiz);
        $siswaId = session('user_id');

        $hasil = HasilKuisSiswa::where('id_quiz', $idQuiz)
                               ->where('id_siswa', $siswaId)
                               ->firstOrFail();

        $reviewDetails = session('quiz_review_' . $idQuiz, null);
        if (!$reviewDetails) {
            $quizSoals = SoalQuiz::where('id_quiz', $idQuiz)->take(5)->get();
            $reviewDetails = $quizSoals->map(function ($s) {
                return [
                    'id_soal' => $s->id_soal,
                    'pertanyaan' => $s->pertanyaan,
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

        return view('siswa.quiz.result', compact('quiz', 'hasil', 'reviewDetails'));
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
