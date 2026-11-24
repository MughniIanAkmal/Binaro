<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\HasilKuisSiswa;
use App\Features\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends ApiController
{
    public function quizDetail(Request $request, $id = null)
    {
        $quiz = null;
        if ($id) {
            $quiz = Quiz::with(['mataPelajaran', 'soal'])->find($id);
        }
        if (!$quiz) {
            $quiz = Quiz::with(['mataPelajaran', 'soal'])->first();
        }
        if (!$quiz) {
            return $this->error('Kuis belum tersedia di database', 404);
        }

        $soals = [];
        foreach ($quiz->soal as $index => $s) {
            $metaGambar = null;
            if ($s->gambar) {
                $metaGambar = json_decode($s->gambar, true);
            }
            $tips = $metaGambar['tips'] ?? 'Pilih jawaban yang paling tepat sesuai materi yang telah kamu pelajari.';

            $soals[] = [
                'id_soal' => $s->id_soal,
                'nomor' => $index + 1,
                'pertanyaan' => $s->pertanyaan,
                'ilustrasi' => $metaGambar['diagram'] ?? $metaGambar['ilustrasi'] ?? null,
                'opsi_a' => $s->opsi_a,
                'opsi_b' => $s->opsi_b,
                'opsi_c' => $s->opsi_c,
                'opsi_d' => $s->opsi_d,
                'kunci_jawaban' => $s->kunci_jawaban,
                'tips' => $tips,
            ];
        }

        return $this->success([
            'id_quiz' => $quiz->id_quiz,
            'judul_quiz' => $quiz->judul_quiz,
            'sub_judul' => $quiz->mataPelajaran?->nama_mapel ?? 'Kuis Mandiri',
            'durasi_menit' => $quiz->durasi_menit ?? 15,
            'durasi_detik' => ($quiz->durasi_menit ?? 15) * 60,
            'countdown_display' => sprintf('%02d:00', $quiz->durasi_menit ?? 15),
            'total_soal' => count($soals),
            'soal' => $soals,
        ], 'Data kuis berhasil dimuat');
    }

    public function quizSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('soal')->find($id);
        if (!$quiz) {
            return $this->error('Kuis tidak ditemukan', 404);
        }

        $jawaban = $request->input('jawaban', []);
        $idSiswa = $this->resolveSiswaId($request) ?? 1;

        $totalSoal = $quiz->soal->count();
        $benar = $salah = 0;

        foreach ($quiz->soal as $s) {
            $jawab = strtoupper(trim($jawaban[$s->id_soal] ?? $jawaban[(string)$s->id_soal] ?? ''));
            if ($jawab && $jawab === strtoupper(trim($s->kunci_jawaban))) {
                $benar++;
            } else {
                $salah++;
            }
        }

        $nilai = $totalSoal > 0 ? round(($benar / $totalSoal) * 100) : 0;
        $bintang = ($nilai >= 80) ? 3 : (($nilai >= 60) ? 2 : 1);

        HasilKuisSiswa::updateOrCreate(
            ['id_quiz' => $quiz->id_quiz, 'id_siswa' => $idSiswa],
            [
                'jumlah_benar' => $benar,
                'jumlah_salah' => $salah,
                'nilai_akhir' => $nilai,
                'waktu_menit' => $request->input('waktu_menit', 3),
                'status_kirim' => 1,
                'waktu_kirim' => \Carbon\Carbon::now(),
            ]
        );

        return $this->success([
            'nilai_akhir' => $nilai,
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'total_soal' => $totalSoal,
            'bintang' => $bintang,
            'pesan' => $nilai >= 80 ? 'Luar biasa! Kamu memahami materi dengan sangat baik!' : 'Bagus! Terus berlatih agar semakin hebat!',
        ], 'Kuis berhasil dikumpulkan!');
    }

    protected function resolveSiswaId(Request $request): ?int
    {
        $user = null;
        try {
            if (config('auth.guards.sanctum')) {
                $user = $request->user('sanctum');
            }
        } catch (\Throwable $e) {}
        if (! $user) $user = $request->user();
        if ($user && isset($user->id_siswa)) return (int) $user->id_siswa;
        $id = $request->input('id_siswa') ?? $request->query('id_siswa') ?? $request->query('id');
        if ($id) return (int) $id;
        return \App\Features\Models\Siswa::first()?->id_siswa;
    }
}
