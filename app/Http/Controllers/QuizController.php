<?php

namespace App\Http\Controllers;

use App\Models\HasilKuisSiswa;
use App\Models\Quiz;
use App\Models\SoalQuiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    // Bank soal index
    public function bankSoal($idQuiz)
    {
        $quiz = Quiz::with(['subBab.bab.mataPelajaran', 'soal'])->findOrFail($idQuiz);
        return view('guru.quiz.bank', compact('quiz'));
    }

    // Store manual soal
    public function storeSoal(Request $request, $idQuiz)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'kunci_jawaban' => 'required|in:A,B,C,D',
        ]);

        SoalQuiz::create([
            'id_quiz' => $idQuiz,
            'pertanyaan' => trim($request->pertanyaan),
            'opsi_a' => trim($request->opsi_a),
            'opsi_b' => trim($request->opsi_b),
            'opsi_c' => trim($request->opsi_c),
            'opsi_d' => trim($request->opsi_d),
            'kunci_jawaban' => strtoupper($request->kunci_jawaban),
        ]);

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    // Delete single soal
    public function destroySoal($idSoal)
    {
        SoalQuiz::findOrFail($idSoal)->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }

    // Import Soal via CSV/Excel
    public function importSoal(Request $request, $idQuiz)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $file = $request->file('file_excel');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Gagal membaca file.');
        }

        $header = fgetcsv($handle, 1000, ',');
        $rowCount = 0;
        $inserted = 0;
        $errors = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $rowCount++;
            if ($rowCount > 50) {
                $errors[] = "Batas maksimal 50 soal terlampaui.";
                break;
            }

            if (count($data) < 6) {
                $errors[] = "Baris #{$rowCount}: Kolom kurang dari 6.";
                continue;
            }

            [$pertanyaan, $a, $b, $c, $d, $kunci] = array_map('trim', array_slice($data, 0, 6));
            $kunciUpper = strtoupper($kunci);

            if (empty($pertanyaan) || empty($a) || empty($b) || empty($c) || empty($d)) {
                $errors[] = "Baris #{$rowCount}: Kolom pertanyaan atau opsi ada yang kosong.";
                continue;
            }

            if (!in_array($kunciUpper, ['A', 'B', 'C', 'D'])) {
                $errors[] = "Baris #{$rowCount}: Kunci jawaban ('{$kunci}') harus A, B, C, atau D.";
                continue;
            }

            SoalQuiz::create([
                'id_quiz' => $idQuiz,
                'pertanyaan' => $pertanyaan,
                'opsi_a' => $a,
                'opsi_b' => $b,
                'opsi_c' => $c,
                'opsi_d' => $d,
                'kunci_jawaban' => $kunciUpper,
            ]);
            $inserted++;
        }

        fclose($handle);

        if (!empty($errors)) {
            return back()->with('error', "Import selesai dengan catatan: " . implode(' ', array_slice($errors, 0, 3)));
        }

        return back()->with('success', "Berhasil mengimpor {$inserted} soal.");
    }

    // Download CSV template
    public function downloadTemplate()
    {
        $filename = "template_soal_kuis.csv";
        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban']);
            fputcsv($file, ['Berapakah 2 + 2?', '3', '4', '5', '6', 'B']);
            fputcsv($file, ['Ibu kota Indonesia adalah?', 'Bandung', 'Surabaya', 'Jakarta', 'Medan', 'C']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Rekap Nilai Kuis Guru
    public function rekap(Request $request)
    {
        $quizzes = Quiz::with('subBab.bab.mataPelajaran')->get();
        $selectedQuizId = $request->query('quiz_id', $quizzes->first()?->id_quiz);

        $hasils = collect();
        if ($selectedQuizId) {
            $hasils = HasilKuisSiswa::with('siswa')
                                    ->where('id_quiz', $selectedQuizId)
                                    ->latest()
                                    ->get();
        }

        return view('guru.quiz.rekap', compact('quizzes', 'selectedQuizId', 'hasils'));
    }

    // Export Rekap Nilai to Excel / CSV stream
    public function exportRekap($idQuiz)
    {
        $quiz = Quiz::with('subBab.bab.mataPelajaran')->findOrFail($idQuiz);
        $hasils = HasilKuisSiswa::with('siswa')->where('id_quiz', $idQuiz)->get();

        $filename = "rekap_nilai_kuis_" . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($quiz->judul_quiz)) . ".csv";
        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($quiz, $hasils) {
            $file = fopen('php://output', 'w');
            // Title block
            fputcsv($file, ["REKAP NILAI KUIS: " . strtoupper($quiz->judul_quiz)]);
            fputcsv($file, ["Mata Pelajaran", $quiz->subBab?->bab?->mataPelajaran?->nama_mapel ?? '-']);
            fputcsv($file, ["Bab / Sub-Bab", ($quiz->subBab?->bab?->nama_bab ?? '-') . " / " . ($quiz->subBab?->nama_sub_bab ?? '-')]);
            fputcsv($file, []);
            // Headers
            fputcsv($file, ['No', 'NISN', 'Nama Siswa', 'Benar', 'Salah', 'Nilai Akhir', 'Waktu Submit']);

            foreach ($hasils as $idx => $row) {
                fputcsv($file, [
                    $idx + 1,
                    $row->siswa?->nisn ?? '-',
                    $row->siswa?->nm_siswa ?? '-',
                    $row->jumlah_benar,
                    $row->jumlah_salah,
                    $row->nilai_akhir,
                    $row->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
