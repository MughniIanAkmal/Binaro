<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\HasilKuisSiswa;
use App\Features\Models\Quiz;
use App\Features\Models\Siswa;
use Illuminate\Http\Request;

class UjianController extends ApiController
{
    public function ujianIndex(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        if (! $siswaId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $allQuizzes = Quiz::whereNull('id_sub_bab')
            ->with(['mataPelajaran', 'guru', 'soal',
                'hasilSiswa' => function ($q) use ($siswaId) {
                    if ($siswaId) {
                        $q->where('id_siswa', $siswaId);
                    }
                },
            ])->latest('id_quiz')->get();

        $ujianTersedia = [];
        $ujianSelesai = [];

        foreach ($allQuizzes as $q) {
            $hasil = $q->hasilSiswa->first();
            $mapelNama = $q->mataPelajaran?->nama_mapel ?? 'Umum';
            $soalCount = $q->soal->count();

            if (! $hasil) {
                $ujianTersedia[] = [
                    'id_quiz' => $q->id_quiz,
                    'judul_quiz' => $q->judul_quiz,
                    'sub_judul' => $q->deskripsi,
                    'mapel' => $mapelNama,
                    'guru' => $q->guru?->nama_guru ?? 'Guru Pengampu',
                    'durasi_menit' => $q->durasi_menit ?? 60,
                    'total_soal' => $soalCount,
                    'tipe_soal' => 'Pilihan Ganda',
                    'badge_mapel' => $mapelNama,
                    'badge_status' => $soalCount > 0 ? 'Bisa Dikerjakan' : 'Belum Ada Soal',
                    'badge_jadwal' => 'Hari Ini',
                    'is_bisa_dikerjakan' => ($soalCount > 0),
                    'keterangan_tombol' => $soalCount > 0 ? 'Mulai Ujian' : 'Belum Ada Soal',
                ];
            } else {
                $isPublished = (bool) ($hasil->status_kirim ?? false);
                $nilai = (int) $hasil->nilai_akhir;
                $ujianSelesai[] = [
                    'id_quiz' => $q->id_quiz,
                    'id_hasil' => $hasil->id_hasil,
                    'judul_quiz' => $q->judul_quiz,
                    'mapel' => $mapelNama,
                    'tanggal' => $hasil->created_at?->format('d M') ?? 'Baru saja',
                    'is_published' => $isPublished,
                    'keterangan' => $isPublished ? 'Selesai' : 'Terkumpul (Menunggu Nilai)',
                    'nilai' => $isPublished ? $nilai : null,
                    'badge_nilai' => $isPublished ? "★ {$nilai}" : 'Menunggu Nilai',
                ];
            }
        }

        $latestHasil = HasilKuisSiswa::with(['quiz.mataPelajaran'])
            ->whereHas('quiz', fn ($q) => $q->whereNull('id_sub_bab'))
            ->where('id_siswa', $siswaId)
            ->where('status_kirim', true)
            ->latest('id_hasil')
            ->first();

        $nilaiTerakhirData = null;
        if ($latestHasil && $latestHasil->quiz) {
            $nilaiAkhir = (int) $latestHasil->nilai_akhir;
            $nilaiTerakhirData = [
                'id_quiz' => $latestHasil->id_quiz,
                'mapel' => $latestHasil->quiz->mataPelajaran?->nama_mapel ?? 'Umum',
                'judul' => $latestHasil->quiz->judul_quiz,
                'tanggal' => $latestHasil->created_at?->format('d M') ?? ''.' • Selesai',
                'nilai' => $nilaiAkhir,
                'badge' => "{$nilaiAkhir} – ".($nilaiAkhir >= 75 ? 'Lulus' : 'Remedial'),
            ];
        }

        return $this->success([
            'banner' => [
                'judul' => 'Penilaian Semester Siswa',
                'subjudul' => 'Baca soal dengan teliti dan jangan lupa berdoa ya!',
            ],
            'tersedia_count' => count($ujianTersedia),
            'selesai_count' => count($ujianSelesai),
            'ujian_tersedia' => $ujianTersedia,
            'ujian_selesai' => $ujianSelesai,
            'nilai_terakhir' => $nilaiTerakhirData,
            'tips' => 'Kerjakan soal yang mudah dulu ya, agar waktumu cukup dan nilaimu maksimal!',
        ], 'Daftar ujian berhasil dimuat');
    }

    public function ujianPetunjuk(Request $request, $id)
    {
        $quiz = Quiz::with(['guru', 'mataPelajaran', 'soal'])->find($id);
        if (! $quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with(['guru', 'mataPelajaran', 'soal'])->first();
        }
        if (! $quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }

        return $this->success([
            'id_quiz' => (int) $quiz->id_quiz,
            'kategori_badge' => strtoupper($quiz->tingkat_level ?? 'Ujian Online'),
            'judul_quiz' => $quiz->judul_quiz,
            'guru' => $quiz->guru?->nama_guru ?? 'Guru Pengampu',
            'ringkasan' => [
                'waktu_menit' => $quiz->durasi_menit ?? 60,
                'total_soal' => $quiz->soal->count(),
                'target_kkm' => 75,
                'koreksi' => 'Otomatis',
            ],
            'petunjuk_pengerjaan' => [
                ['nomor' => 1, 'warna' => 'blue', 'teks' => 'Berdoa sebelum mulai.'],
                ['nomor' => 2, 'warna' => 'orange', 'teks' => 'Pilih salah satu jawaban A, B, C, atau D.'],
                ['nomor' => 3, 'warna' => 'green', 'teks' => 'Periksa kembali lalu kumpulkan.'],
            ],
            'tips_semangat' => [
                'judul' => 'Tips Semangat Belajar',
                'pesan' => 'Tenang, teliti, dan jangan lupa berdoa ya!',
            ],
            'footer' => 'Ada kendala saat membuka soal? Beritahu guru pengawasmu',
        ], 'Petunjuk ujian berhasil dimuat');
    }

    public function ujianPlay(Request $request, $id)
    {
        $quiz = Quiz::with(['mataPelajaran', 'soal'])->find($id);
        if (! $quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with(['mataPelajaran', 'soal'])->first();
        }
        if (! $quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }
        if ($quiz->soal->isEmpty()) {
            return $this->error('Guru belum menginput butir soal untuk ujian ini.', 400);
        }

        $soals = [];
        foreach ($quiz->soal as $index => $s) {
            $meta = [];
            if ($s->gambar) {
                $meta = json_decode($s->gambar, true) ?: [];
            }
            $soals[] = [
                'id_soal' => $s->id_soal,
                'nomor' => $index + 1,
                'tag' => $meta['tag'] ?? ($quiz->mataPelajaran?->nama_mapel ?? 'Soal Ujian'),
                'poin' => $s->bobot_nilai ?? 10,
                'pertanyaan' => $s->pertanyaan,
                'diagram' => $meta['diagram'] ?? null,
                'foto' => $meta['foto'] ?? null,
                'opsi_a' => $s->opsi_a,
                'opsi_b' => $s->opsi_b,
                'opsi_c' => $s->opsi_c,
                'opsi_d' => $s->opsi_d,
            ];
        }

        $durasiMenit = $quiz->durasi_menit ?? 60;

        return $this->success([
            'id_quiz' => (int) $quiz->id_quiz,
            'judul_quiz' => $quiz->judul_quiz,
            'durasi_menit' => $durasiMenit,
            'durasi_detik' => $durasiMenit * 60,
            'countdown_display' => sprintf('%02d:00', $durasiMenit),
            'sisa_detik' => $durasiMenit * 60,
            'total_soal' => count($soals),
            'default_index' => 0,
            'soal' => $soals,
        ], 'Soal ujian berhasil dimuat');
    }

    public function ujianSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('soal')->find($id);
        if (! $quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with('soal')->first();
        }
        if (! $quiz) {
            return $this->error('Ujian tidak ditemukan di database', 404);
        }

        $jawaban = $request->input('jawaban', []);
        $idSiswa = $this->resolveSiswaId($request);
        if (! $idSiswa) {
            return $this->error('User tidak terautentikasi', 401);
        }
        $totalSoal = $quiz->soal->count();
        $benar = $salah = 0;

        foreach ($quiz->soal as $s) {
            $jawab = strtoupper(trim($jawaban[$s->id_soal] ?? $jawaban[(string) $s->id_soal] ?? ''));
            if ($jawab && $jawab === strtoupper(trim($s->kunci_jawaban))) {
                $benar++;
            } else {
                $salah++;
            }
        }

        $nilai = $totalSoal > 0 ? round(($benar / $totalSoal) * 100) : 0;
        $waktuMenit = $request->input('waktu_menit', 10);

        HasilKuisSiswa::updateOrCreate(
            ['id_quiz' => (int) $quiz->id_quiz, 'id_siswa' => $idSiswa],
            [
                'jumlah_benar' => $benar,
                'jumlah_salah' => $salah,
                'nilai_akhir' => $nilai,
                'waktu_menit' => $waktuMenit,
                'status_kirim' => 0,
                'catatan_guru' => $nilai >= 75 ? 'Hasil ujian sangat baik, pertahankan!' : 'Perlu belajar lebih giat lagi.',
                'waktu_kirim' => null,
            ]
        );

        return $this->success([
            'id_quiz' => (int) $quiz->id_quiz,
            'is_published' => false,
            'status_publikasi' => 'Menunggu Pengumuman Nilai dari Guru',
            'pesan_banner' => 'Ujian Berhasil Dikumpulkan!',
            'sub_pesan' => 'Jawabanmu telah tersimpan dengan aman. Nilai dan rincian benar/salah akan muncul setelah diumumkan oleh guru.',
            'durasi_menit' => $waktuMenit,
            'target_kkm' => 75,
        ], 'Ujian berhasil dikumpulkan! Menunggu pengumuman nilai dari guru.');
    }

    public function ujianResult(Request $request, $id)
    {
        $quiz = Quiz::with(['guru', 'mataPelajaran'])->find($id);
        if (! $quiz) {
            $quiz = Quiz::whereNull('id_sub_bab')->with(['guru', 'mataPelajaran'])->first();
        }

        $siswaId = $this->resolveSiswaId($request);
        if (! $siswaId) {
            return $this->error('User tidak terautentikasi', 401);
        }
        $siswa = Siswa::find($siswaId);

        $hasil = null;
        if ($quiz) {
            $hasil = HasilKuisSiswa::where('id_quiz', $quiz->id_quiz)
                ->where('id_siswa', $siswaId)->latest('id_hasil')->first();
        }
        if (! $hasil) {
            $hasil = HasilKuisSiswa::where('id_siswa', $siswaId)->latest('id_hasil')->first();
        }
        if (! $hasil) {
            return $this->error('Hasil ujian belum ditemukan untuk siswa ini', 404);
        }

        $isPublished = (bool) ($hasil->status_kirim ?? false);
        $waktu = $hasil->waktu_menit ?? 10;

        if (! $isPublished) {
            return $this->success([
                'id_quiz' => (int) ($quiz?->id_quiz ?? $hasil->id_quiz),
                'nama_siswa' => $siswa?->nm_siswa ?? 'Siswa',
                'is_published' => false,
                'status_publikasi' => 'Menunggu Pengumuman Nilai dari Guru',
                'pesan_banner' => 'Ujian Selesai Dikumpulkan!',
                'sub_pesan' => 'Jawabanmu telah tersimpan rapi di sistem. Nilai dan rincian benar/salah akan muncul secara otomatis segera setelah guru mengumumkan nilai ke seluruh siswa.',
                'durasi_menit' => $waktu,
                'nilai_akhir' => null,
                'jumlah_benar' => null,
                'jumlah_salah' => null,
                'is_lulus_kkm' => null,
                'label_kkm' => 'Menunggu Pengumuman',
                'status_teks' => 'Menunggu Nilai dari Guru',
                'mapel' => [
                    'nama' => $quiz?->judul_quiz ?? 'Ujian Online',
                    'guru' => $quiz?->guru?->nama_guru ?? 'Guru Pengampu',
                    'catatan_guru' => 'Nilai ujian sedang dalam proses verifikasi oleh bapak/ibu guru pengampu.',
                ],
            ], 'Ujian telah dikumpulkan, nilai menunggu pengumuman dari guru');
        }

        $nilai = (int) $hasil->nilai_akhir;
        $benar = (int) $hasil->jumlah_benar;
        $salah = (int) $hasil->jumlah_salah;

        return $this->success([
            'id_quiz' => (int) ($quiz?->id_quiz ?? $hasil->id_quiz),
            'nama_siswa' => $siswa?->nm_siswa ?? 'Siswa',
            'is_published' => true,
            'status_publikasi' => 'Nilai Resmi Diumumkan',
            'pesan_banner' => 'Nilai Ujian Telah Diumumkan!',
            'sub_pesan' => 'Berikut adalah hasil skor resmi dari bapak/ibu guru pengampu.',
            'target_kkm' => 75,
            'is_lulus_kkm' => ($nilai >= 75),
            'label_kkm' => ($nilai >= 75) ? 'LULUS KKM (Target: 75)' : 'BELUM LULUS (Target: 75)',
            'nilai_akhir' => $nilai,
            'status_teks' => $nilai >= 85 ? 'Sangat Baik & Membanggakan' : ($nilai >= 75 ? 'Lulus & Memuaskan' : 'Perlu Remedial'),
            'jumlah_benar' => $benar,
            'jumlah_salah' => $salah,
            'durasi_menit' => $waktu,
            'mapel' => [
                'nama' => $quiz?->judul_quiz ?? 'Ujian Online',
                'guru' => $quiz?->guru?->nama_guru ?? 'Guru Pengampu',
                'catatan_guru' => $hasil->catatan_guru ?? 'Tetap semangat dalam belajar!',
            ],
        ], 'Hasil ujian berhasil dimuat');
    }
}
