<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilKuisSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaUjianOnlineDesktopTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $mapel;
    protected $kelas;
    protected $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Guru::create([
            'nip' => '199001012020011001',
            'nama_guru' => 'Pak Budi Hartono, M.Pd',
            'password' => 'guru123',
        ]);

        $this->mapel = MataPelajaran::create([
            'nama_mapel' => 'Ilmu Pengetahuan Alam',
            'deskripsi' => 'Mapel IPA SD',
        ]);

        $this->kelas = Kelas::create([
            'pararel' => 'Kelas 5-B',
            'tingkat' => 5,
        ]);

        $this->siswa = Siswa::create([
            'nm_siswa' => 'Rian Hidayat',
            'nisn' => '1234567890',
            'id_rooms' => $this->kelas->id_rooms,
            'password' => 'siswa123',
        ]);
    }

    public function test_siswa_can_view_desktop_ujian_index_with_teacher_inputted_data()
    {
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Penilaian Tengah Semester IPA',
            'tingkat_level' => 'susah',
            'durasi_menit'  => 75,
            'target_tipe'   => 'semua',
            'deskripsi'     => 'Ujian materi ekosistem dan rantai makanan.',
        ]);

        SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Organisme yang berperan sebagai produsen adalah?',
            'opsi_a'        => 'Tumbuhan hijau',
            'opsi_b'        => 'Kelinci',
            'opsi_c'        => 'Ular',
            'opsi_d'        => 'Elang',
            'kunci_jawaban' => 'A',
            'bobot_nilai'   => 20,
        ]);

        $response = $this->withSession(['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa])
            ->get(route('siswa.ujian.index'));

        $response->assertOk();
        $response->assertSee('Ujian Online Siswa');
        $response->assertSee('Penilaian Tengah Semester IPA');
        $response->assertSee('Ilmu Pengetahuan Alam');
        $response->assertSee('Pak Budi Hartono, M.Pd');
        $response->assertSee('75 Menit');
        $response->assertSee('Level Sulit');
        // Contains Petunjuk Modal
        $response->assertSee('modal-petunjuk-ujian');
        $response->assertSee('Mulai Kerjakan Sekarang');
    }

    public function test_siswa_plays_cbt_exam_and_sees_all_teacher_questions_without_dummy_relics()
    {
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Ulangan Harian Bab Gaya',
            'tingkat_level' => 'sedang',
            'durasi_menit'  => 45,
            'target_tipe'   => 'semua',
        ]);

        // Input 3 distinct questions from teacher
        $soal1 = SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Apa satuan gaya dalam SI?',
            'opsi_a'        => 'Newton (N)',
            'opsi_b'        => 'Joule (J)',
            'opsi_c'        => 'Watt (W)',
            'opsi_d'        => 'Pascal (Pa)',
            'kunci_jawaban' => 'A',
            'bobot_nilai'   => 10,
        ]);

        $soal2 = SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Gaya yang terjadi ketika dua permukaan saling bersentuhan disebut?',
            'opsi_a'        => 'Gaya Gravitasi',
            'opsi_b'        => 'Gaya Gesek',
            'opsi_c'        => 'Gaya Magnet',
            'opsi_d'        => 'Gaya Listrik',
            'kunci_jawaban' => 'B',
            'bobot_nilai'   => 15,
        ]);

        $soal3 = SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Buah kelapa jatuh dari pohon ke tanah karena gaya?',
            'opsi_a'        => 'Gaya Otot',
            'opsi_b'        => 'Gaya Pegas',
            'opsi_c'        => 'Gaya Gravitasi Bumi',
            'opsi_d'        => 'Gaya Mesin',
            'kunci_jawaban' => 'C',
            'bobot_nilai'   => 10,
        ]);

        $session = ['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa];

        $response = $this->withSession($session)
            ->get(route('siswa.quiz.play', $quiz->id_quiz));

        $response->assertOk();
        // Verify all 3 questions inputted by teacher are present
        $response->assertSee('Apa satuan gaya dalam SI?');
        $response->assertSee('Gaya Gesek');
        $response->assertSee('Buah kelapa jatuh dari pohon ke tanah karena gaya?');
        $response->assertSee('Gaya Gravitasi Bumi');

        // Verify the fake watermelon diagram is NOT rendered
        $response->assertDontSee('DIAGRAM PECAHAN 8 POTONG');
        $response->assertDontSee('Dimakan Budi (3)');

        // Verify CBT desktop matrix sidebar is present
        $response->assertSee('Daftar Nomor Soal');
        $response->assertSee('Selesaikan Ujian');
        $response->assertViewHas('soals', function ($soals) {
            return count($soals) === 3;
        });
    }

    public function test_siswa_submits_exam_and_views_desktop_result_review()
    {
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Kuis Ekosistem',
            'tingkat_level' => 'sedang',
            'durasi_menit'  => 30,
            'target_tipe'   => 'semua',
        ]);

        $soal1 = SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Rantai makanan dimulai dari?',
            'opsi_a'        => 'Produsen',
            'opsi_b'        => 'Konsumen 1',
            'opsi_c'        => 'Pengurai',
            'opsi_d'        => 'Karnivora',
            'kunci_jawaban' => 'A',
            'bobot_nilai'   => 10,
        ]);

        $soal2 = SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Pengurai dalam ekosistem contohnya adalah?',
            'opsi_a'        => 'Singa',
            'opsi_b'        => 'Bakteri dan Jamur',
            'opsi_c'        => 'Padi',
            'opsi_d'        => 'Belalang',
            'kunci_jawaban' => 'B',
            'bobot_nilai'   => 10,
        ]);

        $session = ['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa];

        // Submit: answer 1 correct (A), answer 2 wrong (C)
        $submitRes = $this->withSession($session)
            ->post(route('siswa.quiz.submit', $quiz->id_quiz), [
                'jawaban' => [
                    $soal1->id_soal => 'A',
                    $soal2->id_soal => 'C',
                ],
            ]);

        $submitRes->assertRedirect(route('siswa.quiz.result', $quiz->id_quiz));

        $hasil = HasilKuisSiswa::where('id_quiz', $quiz->id_quiz)
            ->where('id_siswa', $this->siswa->id_siswa)
            ->first();

        $this->assertNotNull($hasil);
        $this->assertEquals(1, $hasil->jumlah_benar);
        $this->assertEquals(1, $hasil->jumlah_salah);
        $this->assertEquals(50.00, (float)$hasil->nilai_akhir);
        // By default, online exam score is NOT yet published to the student
        $this->assertFalse((bool)$hasil->status_kirim);

        // 1. Result view before teacher publishes: Score number and review are held
        $resultRes = $this->withSession($session)
            ->get(route('siswa.quiz.result', $quiz->id_quiz));

        $resultRes->assertOk();
        $resultRes->assertSee('MENUNGGU PUBLIKASI NILAI OLEH GURU');
        $resultRes->assertSee('Menunggu Guru Mengirimkan Nilai');
        $resultRes->assertDontSee('PERLU REMEDIAL');

        // 2. Guru clicks "Kirim Nilai ke Semua Siswa" from rekap ujian
        $kirimRes = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru])
            ->post(route('rekap_ujian.kirim_semua', $quiz->id_quiz));

        $kirimRes->assertSessionHas('success');
        $hasil->refresh();
        $this->assertTrue((bool)$hasil->status_kirim);

        // 3. Result view after teacher publishes: Score, grade status, and review are now visible!
        $publishedRes = $this->withSession($session)
            ->get(route('siswa.quiz.result', $quiz->id_quiz));

        $publishedRes->assertOk();
        $publishedRes->assertSee('50');
        $publishedRes->assertSee('PERLU REMEDIAL');
        $publishedRes->assertSee('Pak Budi Hartono, M.Pd');
        $publishedRes->assertSee('Rantai makanan dimulai dari?');
        $publishedRes->assertSee('Pengurai dalam ekosistem contohnya adalah?');
    }

    public function test_siswa_can_choose_difficulty_level_and_get_filtered_questions()
    {
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Ujian Sains Bertingkat',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);

        $soalMudah = SoalQuiz::create([
            'id_quiz'          => $quiz->id_quiz,
            'pertanyaan'       => 'Soal Mudah: Apa warna daun pada umumnya?',
            'opsi_a'           => 'Hijau',
            'opsi_b'           => 'Biru',
            'opsi_c'           => 'Merah',
            'opsi_d'           => 'Hitam',
            'kunci_jawaban'    => 'A',
            'tingkat_kesulitan'=> 'mudah',
        ]);

        $soalSulit = SoalQuiz::create([
            'id_quiz'          => $quiz->id_quiz,
            'pertanyaan'       => 'Soal Sulit: Proses fotolisis air terjadi pada tahap?',
            'opsi_a'           => 'Reaksi Terang',
            'opsi_b'           => 'Reaksi Gelap',
            'opsi_c'           => 'Siklus Calvin',
            'opsi_d'           => 'Glikolisis',
            'kunci_jawaban'    => 'A',
            'tingkat_kesulitan'=> 'sulit',
        ]);

        $session = ['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa];

        // 1. Petunjuk page shows difficulty choices and counts
        $petunjukRes = $this->withSession($session)
            ->get(route('siswa.ujian.petunjuk', $quiz->id_quiz));
        $petunjukRes->assertOk();
        $petunjukRes->assertSee('Pilih Tingkat Kesusahan Soal');
        $petunjukRes->assertSee('Mudah');
        $petunjukRes->assertSee('Sulit');

        // 2. Play with ?kesulitan=mudah
        $playMudahRes = $this->withSession($session)
            ->get(route('siswa.quiz.play', ['idQuiz' => $quiz->id_quiz, 'kesulitan' => 'mudah']));
        $playMudahRes->assertOk();
        $playMudahRes->assertSee('Soal Mudah: Apa warna daun pada umumnya?');
        $playMudahRes->assertDontSee('Soal Sulit: Proses fotolisis air terjadi pada tahap?');
        $playMudahRes->assertSee('🟢 Tingkat Mudah');

        // 3. Play with ?kesulitan=sulit
        $playSulitRes = $this->withSession($session)
            ->get(route('siswa.quiz.play', ['idQuiz' => $quiz->id_quiz, 'kesulitan' => 'sulit']));
        $playSulitRes->assertOk();
        $playSulitRes->assertSee('Soal Sulit: Proses fotolisis air terjadi pada tahap?');
        $playSulitRes->assertDontSee('Soal Mudah: Apa warna daun pada umumnya?');
        $playSulitRes->assertSee('🔴 Tingkat Sulit');

        // 4. Submit only the easy question (1 question attempted -> 1 correct = 100%)
        $submitRes = $this->withSession($session)
            ->post(route('siswa.quiz.submit', $quiz->id_quiz), [
                'soal_ids' => [$soalMudah->id_soal],
                'jawaban'  => [
                    $soalMudah->id_soal => 'A',
                ],
            ]);

        $submitRes->assertRedirect(route('siswa.quiz.result', $quiz->id_quiz));

        $hasil = HasilKuisSiswa::where('id_quiz', $quiz->id_quiz)
            ->where('id_siswa', $this->siswa->id_siswa)
            ->first();

        $this->assertNotNull($hasil);
        $this->assertEquals(1, $hasil->jumlah_benar);
        $this->assertEquals(0, $hasil->jumlah_salah);
        $this->assertEquals(100.00, (float)$hasil->nilai_akhir);

        // Teacher publishes score
        $hasil->update(['status_kirim' => true]);

        // Student result shows difficulty badge
        $resultRes = $this->withSession($session)
            ->get(route('siswa.quiz.result', $quiz->id_quiz));
        $resultRes->assertOk();
        $resultRes->assertSee('🟢 Mudah');
    }
}

