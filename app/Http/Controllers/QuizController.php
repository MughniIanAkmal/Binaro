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
            'pertanyaan'    => 'required|string|max:1000',
            'opsi_a'        => 'required|string|max:255',
            'opsi_b'        => 'required|string|max:255',
            'opsi_c'        => 'required|string|max:255',
            'opsi_d'        => 'required|string|max:255',
            'kunci_jawaban' => 'required|in:A,B,C,D',
        ], [
            'pertanyaan.required' => 'Pertanyaan soal wajib diisi.',
            'pertanyaan.max'      => 'Pertanyaan soal maksimal 1000 karakter.',
            'opsi_a.max'          => 'Pilihan A maksimal 255 karakter.',
            'opsi_b.max'          => 'Pilihan B maksimal 255 karakter.',
            'opsi_c.max'          => 'Pilihan C maksimal 255 karakter.',
            'opsi_d.max'          => 'Pilihan D maksimal 255 karakter.',
        ]);

        $pertanyaanTrim = trim($request->pertanyaan);
        $duplicateSoal = SoalQuiz::where('id_quiz', $idQuiz)
            ->whereRaw('LOWER(TRIM(pertanyaan)) = ?', [mb_strtolower($pertanyaanTrim)])
            ->exists();
        if ($duplicateSoal) {
            return back()->withInput()->with('error', 'Pertanyaan soal yang serupa sudah ada di kuis ini. Silakan buat pertanyaan yang berbeda.');
        }

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

    public function importSoal(Request $request, $idQuiz)
    {
        $excelExtensionValidator = function ($attribute, $value, $fail) {
            if ($value) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                    $fail('File kuis wajib berformat spreadsheet Excel / CSV (.xlsx, .xls, atau .csv). Format selain Excel/CSV tidak diperbolehkan.');
                }
            }
        };

        $request->validate([
            'file_excel' => ['required', 'file', 'max:5120', $excelExtensionValidator],
        ], [
            'file_excel.required' => 'Silakan pilih file Excel / CSV (.xlsx, .xls, .csv) untuk diunggah.',
            'file_excel.file'     => 'File kuis yang diunggah tidak valid.',
            'file_excel.max'      => 'Ukuran file Excel / CSV maksimal 5 MB.',
        ]);

        $file = $request->file('file_excel');
        $filePath = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());

        $rows = \App\Services\ExcelService::parseFile($filePath, $ext);

        if (empty($rows)) {
            return back()->with('error', 'Gagal membaca isi data file Excel / CSV. Pastikan format tabel sesuai template.');
        }

        // Baris pertama diasumsikan sebagai Header kolom
        $header = array_shift($rows);
        $rowCount = 0;
        $inserted = 0;
        $errors = [];

        foreach ($rows as $data) {
            $rowCount++;
            if ($rowCount > 50) {
                $errors[] = "Batas maksimal 50 soal terlampaui.";
                break;
            }

            if (count($data) < 6) {
                $errors[] = "Baris #{$rowCount}: Kolom kurang dari 6 (Pertanyaan, Opsi A-D, Kunci).";
                continue;
            }

            [$pertanyaan, $a, $b, $c, $d, $kunci] = array_map('trim', array_slice($data, 0, 6));
            $kunciUpper = strtoupper($kunci);

            if (empty($pertanyaan) || empty($a) || empty($b) || empty($c) || empty($d)) {
                $errors[] = "Baris #{$rowCount}: Pertanyaan atau pilihan jawaban tidak boleh kosong.";
                continue;
            }

            if (!in_array($kunciUpper, ['A', 'B', 'C', 'D'])) {
                $errors[] = "Baris #{$rowCount}: Kunci jawaban ('{$kunci}') tidak valid. Wajib diisi huruf A, B, C, atau D.";
                continue;
            }

            $isDuplicate = SoalQuiz::where('id_quiz', $idQuiz)
                ->whereRaw('LOWER(TRIM(pertanyaan)) = ?', [mb_strtolower($pertanyaan)])
                ->exists();
            if ($isDuplicate) {
                $errors[] = "Baris #{$rowCount}: Pertanyaan sudah ada di kuis ini (dilewati).";
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

        if (!empty($errors)) {
            return back()->with('error', "Import selesai dengan catatan: " . implode(' ', array_slice($errors, 0, 3)));
        }

        return back()->with('success', "Berhasil mengimpor {$inserted} soal dari file Excel.");
    }

    // Download template Excel (.xlsx)
    public function downloadTemplate()
    {
        $content = \App\Services\ExcelService::generateQuizTemplate();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_soal_kuis.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // Rekap Nilai Kuis & Ujian Guru
    public function rekap(Request $request)
    {
        $quizzes = Quiz::with(['subBab.bab.mataPelajaran', 'mataPelajaran'])->orderBy('id_quiz', 'desc')->get();
        $selectedQuizId = $request->query('quiz_id', $quizzes->first()?->id_quiz);

        $hasils = collect();
        $selectedQuiz = null;
        if ($selectedQuizId) {
            $selectedQuiz = Quiz::with(['subBab.bab.mataPelajaran', 'mataPelajaran'])->find($selectedQuizId);
            $hasils = HasilKuisSiswa::with('siswa')
                                    ->where('id_quiz', $selectedQuizId)
                                    ->latest()
                                    ->get();
        }

        // Use rich rekap-ujian view when accessed via /rekap-ujian or /rekap-nilai URL
        $view = (request()->routeIs('rekap_ujian.*') || request()->routeIs('rekap_nilai.*') || request()->is('rekap-nilai*') || request()->is('rekap-ujian*')) 
            ? 'rekap-ujian.index' 
            : 'guru.quiz.rekap';

        return view($view, compact('quizzes', 'selectedQuizId', 'selectedQuiz', 'hasils'));
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
            fputcsv($file, ['No', 'NISN', 'Nama Siswa', 'Benar', 'Salah', 'Nilai Akhir', 'Waktu (Menit)', 'Keaktifan', 'Catatan Guru', 'Waktu Submit']);

            foreach ($hasils as $idx => $row) {
                fputcsv($file, [
                    $idx + 1,
                    $row->siswa?->nisn ?? '-',
                    $row->siswa?->nm_siswa ?? '-',
                    $row->jumlah_benar,
                    $row->jumlah_salah,
                    $row->nilai_akhir,
                    $row->waktu_menit ?? '-',
                    $row->nilai_keaktifan ? $row->nilai_keaktifan . ' Poin' : '-',
                    $row->catatan_guru ?? '-',
                    $row->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Simpan Umpan Balik Guru untuk Siswa
    public function simpanFeedback(Request $request)
    {
        $request->validate([
            'id_hasil' => 'required|exists:hasil_kuis_siswa,id_hasil',
            'catatan_guru' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'catatan_guru.required' => 'Catatan umpan balik tidak boleh kosong.',
        ]);

        $hasil = HasilKuisSiswa::findOrFail($request->id_hasil);
        $hasil->update([
            'catatan_guru' => $request->catatan_guru,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Umpan balik berhasil disimpan.',
                'catatan_guru' => $hasil->catatan_guru,
            ]);
        }

        return back()->with('success', 'Umpan balik berhasil disimpan.');
    }

    // Kirim Nilai & Umpan Balik ke Notifikasi Siswa
    public function kirimNilai(Request $request)
    {
        $request->validate([
            'id_hasil' => 'required|exists:hasil_kuis_siswa,id_hasil',
            'pesan_tambahan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $hasil = HasilKuisSiswa::with(['siswa', 'quiz'])->findOrFail($request->id_hasil);

        // Kuis materi tidak menggunakan fitur kirim nilai
        if (!empty($hasil->quiz?->id_sub_bab)) {
            return back()->with('error', 'Kuis materi pembelajaran tidak memerlukan rilis nilai. Fitur kirim nilai hanya tersedia untuk Ujian Online resmi.');
        }

        $guruId = session('user_id');
        if (session('user_type') !== 'guru' || !$guruId) {
            $guruId = \App\Models\Guru::value('id_guru');
        }

        $pesan = "Hasil Ujian [{$hasil->quiz?->judul_quiz}]: Nilai Anda adalah " . number_format($hasil->nilai_akhir, 0) . ". ";
        if ($hasil->catatan_guru) {
            $pesan .= "Catatan Guru: {$hasil->catatan_guru}. ";
        }
        if ($request->filled('pesan_tambahan')) {
            $pesan .= "Pesan: " . $request->pesan_tambahan;
        }

        \App\Models\Notifikasi::create([
            'id_guru' => $guruId,
            'id_siswa' => $hasil->id_siswa,
            'id_pr' => null,
            'pesan' => trim($pesan),
            'status_baca' => false,
        ]);

        // Tandai bahwa nilai telah dikirim/dirilis oleh guru
        $hasil->update([
            'status_kirim' => true,
            'waktu_kirim' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai dan umpan balik berhasil dikirim ke siswa.',
            ]);
        }

        return back()->with('success', 'Nilai dan umpan balik berhasil dikirim ke siswa.');
    }

    // Kirim Nilai ke Seluruh Siswa
    public function kirimNilaiSemua(Request $request, $idQuiz)
    {
        $quiz = Quiz::findOrFail($idQuiz);

        // Kuis materi tidak menggunakan fitur kirim nilai
        if (!empty($quiz->id_sub_bab)) {
            return back()->with('error', 'Kuis materi pembelajaran tidak memerlukan rilis nilai. Fitur kirim nilai hanya tersedia untuk Ujian Online resmi.');
        }

        $hasils = HasilKuisSiswa::where('id_quiz', $idQuiz)->get();

        $guruId = session('user_id');
        if (session('user_type') !== 'guru' || !$guruId) {
            $guruId = \App\Models\Guru::value('id_guru');
        }

        $terkirim = 0;
        foreach ($hasils as $h) {
            $pesan = "Hasil Ujian [{$quiz->judul_quiz}]: Nilai akhir Anda adalah " . number_format($h->nilai_akhir, 0) . ". ";
            if ($h->catatan_guru) {
                $pesan .= "Catatan: {$h->catatan_guru}";
            }

            \App\Models\Notifikasi::create([
                'id_guru' => $guruId,
                'id_siswa' => $h->id_siswa,
                'id_pr' => null,
                'pesan' => trim($pesan),
                'status_baca' => false,
            ]);

            // Tandai nilai telah dirilis ke siswa
            $h->update([
                'status_kirim' => true,
                'waktu_kirim' => now(),
            ]);

            $terkirim++;
        }

        return back()->with('success', "Berhasil mengirim nilai ujian ke {$terkirim} siswa.");
    }

    // Jadwalkan Remedial Otomatis untuk Siswa Nilai < KKM (70)
    public function jadwalkanRemedial(Request $request, $idQuiz)
    {
        $quiz = Quiz::findOrFail($idQuiz);

        // Kuis materi tidak memiliki opsi remidi
        if (!empty($quiz->id_sub_bab)) {
            return back()->with('error', 'Kuis materi pembelajaran tidak menyediakan opsi remedial. Sesi remedial hanya berlaku untuk Ujian Online resmi.');
        }

        $remedials = HasilKuisSiswa::where('id_quiz', $idQuiz)
                                    ->where('nilai_akhir', '<', 70)
                                    ->get();

        if ($remedials->isEmpty()) {
            return back()->with('info', 'Tidak ada siswa yang memerlukan remedial untuk ujian ini.');
        }

        $guruId = session('user_id');
        if (session('user_type') !== 'guru' || !$guruId) {
            $guruId = \App\Models\Guru::value('id_guru');
        }

        foreach ($remedials as $h) {
            \App\Models\Notifikasi::create([
                'id_guru' => $guruId,
                'id_siswa' => $h->id_siswa,
                'id_pr' => null,
                'pesan' => "Pemberitahuan Remedial [{$quiz->judul_quiz}]: Anda dijadwalkan mengikuti sesi remedial penguatan materi. Silakan pelajari kembali bab terkait di menu Pembelajaran.",
                'status_baca' => false,
            ]);
        }

        return back()->with('success', "Jadwal remedial berhasil diterbitkan dan dinotifikasikan ke {$remedials->count()} siswa.");
    }
}
