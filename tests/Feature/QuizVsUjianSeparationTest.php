<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Bab;
use App\Models\SubBab;
use App\Models\Materi;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use App\Models\HasilKuisSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizVsUjianSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $mapel;
    protected $kelas;
    protected $siswa;
    protected $bab;
    protected $subBab;
    protected $materiKuis;
    protected $quizMateri;
    protected $ujianOnline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapel = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'IPA Kelas 4'],
            ['deskripsi' => 'Mapel IPA']
        );

        $this->guru = Guru::firstOrCreate(
            ['nip' => '198203042008011005'],
            ['nama_guru' => 'Bu Guru IPA, S.Pd', 'email' => 'guru.ipa@binaro.sch.id', 'password' => 'password']
        );

        $this->kelas = Kelas::firstOrCreate(
            ['pararel' => 'Kelas 4-B'],
            ['tingkat' => 4]
        );

        $this->siswa = Siswa::create([
            'nm_siswa' => 'Rizky Pratama',
            'nisn' => '0098765432',
            'id_rooms' => $this->kelas->id_rooms,
            'jk' => 'L',
            'tgl_lahir' => '2013-02-14',
            'password' => 'password',
        ]);

        $this->bab = Bab::create([
            'id_mapel' => $this->mapel->id_mapel,
            'nama_bab' => 'Bab 1 Makhluk Hidup',
        ]);

        $this->subBab = SubBab::create([
            'id_bab' => $this->bab->id_bab,
            'nama_sub_bab' => 'Sub-Bab 1.1 Ciri-Ciri Hewan',
        ]);

        // 1. Kuis Materi (Evaluasi Pembelajaran Sub-Bab)
        $this->quizMateri = Quiz::create([
            'id_sub_bab' => $this->subBab->id_sub_bab,
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'judul_quiz' => 'Kuis Ciri-Ciri Hewan',
            'nama_quiz' => 'Kuis Ciri-Ciri Hewan',
        ]);

        $this->materiKuis = Materi::create([
            'id_bab' => $this->bab->id_bab,
            'id_sub_bab' => $this->subBab->id_sub_bab,
            'id_quiz' => $this->quizMateri->id_quiz,
            'judul_materi' => 'Kuis Evaluasi Ciri Hewan',
            'tipe_materi' => 'kuis',
        ]);

        SoalQuiz::create([
            'id_quiz' => $this->quizMateri->id_quiz,
            'pertanyaan' => 'Hewan yang bernapas dengan insang adalah?',
            'opsi_a' => 'Ikan Mas',
            'opsi_b' => 'Kucing',
            'opsi_c' => 'Burung Merpati',
            'opsi_d' => 'Kelinci',
            'kunci_jawaban' => 'A',
        ]);

        // 2. Ujian Online Resmi (CBT Ujian Sekolah / UTS / UAS)
        $this->ujianOnline = Quiz::create([
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'id_sub_bab' => null, // Standalone exam
            'judul_quiz' => 'Ujian Tengah Semester Ganjil IPA',
            'nama_quiz' => 'Ujian Tengah Semester Ganjil IPA',
            'tingkat_level' => 'sedang',
            'durasi_menit' => 90,
            'target_tipe' => 'semua',
        ]);

        SoalQuiz::create([
            'id_quiz' => $this->ujianOnline->id_quiz,
            'pertanyaan' => 'Berikut yang merupakan produsen dalam rantai makanan adalah?',
            'opsi_a' => 'Padi',
            'opsi_b' => 'Belalang',
            'opsi_c' => 'Katak',
            'opsi_d' => 'Ular',
            'kunci_jawaban' => 'A',
        ]);
    }

    public function test_daftar_ujian_siswa_only_contains_official_exams_not_materi_quizzes()
    {
        $session = ['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa];

        $response = $this->withSession($session)->get(route('siswa.ujian.index'));
        $response->assertStatus(200);

        // Ujian resmi harus muncul
        $response->assertSee('Ujian Tengah Semester Ganjil IPA');

        // Kuis materi TIDAK BOLEH muncul di daftar Ujian Online
        $response->assertDontSee('Kuis Ciri-Ciri Hewan');
    }

    public function test_kelola_ujian_guru_only_contains_official_exams_not_materi_quizzes()
    {
        $guruSession = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];

        $response = $this->withSession($guruSession)->get(route('guru.ujian.index'));
        $response->assertStatus(200);

        // Ujian resmi harus muncul
        $response->assertSee('Ujian Tengah Semester Ganjil IPA');

        // Kuis materi TIDAK BOLEH muncul di Kelola Ujian Online Guru
        $response->assertDontSee('Kuis Ciri-Ciri Hewan');
    }

    public function test_student_taking_materi_quiz_enters_kuis_materi_flow_not_ujian_cbt()
    {
        $session = ['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa];

        // 1. Dari halaman materi, siswa melihat opsi pengerjaan kuis materi
        $materiRes = $this->withSession($session)->get(route('siswa.materi.view', $this->materiKuis->id_materi));
        $materiRes->assertStatus(200);
        $materiRes->assertSee('Kuis Evaluasi Sub-Bab');
        $materiRes->assertSee(route('siswa.quiz.play', $this->quizMateri->id_quiz));

        // 2. Masuk ke halaman pengerjaan kuis materi
        $playRes = $this->withSession($session)->get(route('siswa.quiz.play', $this->quizMateri->id_quiz));
        $playRes->assertStatus(200);

        // Harus menampilkan teks Kuis Materi
        $playRes->assertSee('Kuis Materi Pembelajaran');
        $playRes->assertSee('Selesaikan Kuis Materi');
        // Tombol kembali harus mengarah ke materi belajar, BUKAN ke daftar ujian!
        $playRes->assertSee(route('siswa.materi.view', $this->materiKuis->id_materi));
        $playRes->assertDontSee('Kembali ke Daftar Ujian');
        $playRes->assertDontSee('CBT Online');

        // 3. Siswa mengirimkan jawaban kuis materi
        $soal = $this->quizMateri->soal->first();
        $submitRes = $this->withSession($session)->post(route('siswa.quiz.submit', $this->quizMateri->id_quiz), [
            'jawaban' => [
                $soal->id_soal => 'A', // Jawaban Benar
            ],
        ]);

        $submitRes->assertRedirect(route('siswa.quiz.result', $this->quizMateri->id_quiz));

        // 4. Di halaman hasil kuis materi:
        $resultRes = $this->withSession($session)->get(route('siswa.quiz.result', $this->quizMateri->id_quiz));
        $resultRes->assertStatus(200);

        // Nilai langsung keluar karena ini kuis formatif materi (TIDAK menunggu kirim nilai ujian)
        $resultRes->assertSee('Hasil Kuis Pemahaman Materi');
        $resultRes->assertSee('100');
        $resultRes->assertSee('Jawaban Benar');
        $resultRes->assertSee('Kembali ke Materi Belajar');
        // Tidak boleh ada teks ujian atau menunggu publikasi ujian
        $resultRes->assertDontSee('MENUNGGU PUBLIKASI NILAI OLEH GURU');
        $resultRes->assertDontSee('Kembali ke Daftar Ujian');
    }

    public function test_student_taking_official_exam_enters_cbt_flow_and_waits_for_teacher_score_release()
    {
        $session = ['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa];

        // 1. Masuk ke petunjuk ujian resmi
        $petunjukRes = $this->withSession($session)->get(route('siswa.ujian.petunjuk', $this->ujianOnline->id_quiz));
        $petunjukRes->assertStatus(200);
        $petunjukRes->assertSee('Petunjuk Pengerjaan');

        // 2. Masuk ke lembar ujian resmi
        $playRes = $this->withSession($session)->get(route('siswa.ujian.play', $this->ujianOnline->id_quiz));
        $playRes->assertStatus(200);
        $playRes->assertSee('CBT Online');
        $playRes->assertSee('Kembali ke Daftar Ujian');

        // 3. Submit lembar jawaban ujian resmi
        $soal = $this->ujianOnline->soal->first();
        $submitRes = $this->withSession($session)->post(route('siswa.ujian.submit', $this->ujianOnline->id_quiz), [
            'jawaban' => [
                $soal->id_soal => 'A',
            ],
        ]);

        $submitRes->assertRedirect(route('siswa.ujian.result', $this->ujianOnline->id_quiz));

        // 4. Hasil ujian resmi masih ditahan (menunggu guru klik "Kirim Nilai ke Semua Siswa")
        $resultRes = $this->withSession($session)->get(route('siswa.ujian.result', $this->ujianOnline->id_quiz));
        $resultRes->assertStatus(200);
        $resultRes->assertSee('MENUNGGU PUBLIKASI NILAI OLEH GURU');
        $resultRes->assertSee('Kembali ke Daftar Ujian');
    }

    public function test_sidebar_highlights_pembelajaran_during_quiz_and_ujian_during_exam()
    {
        $session = ['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $this->siswa->nm_siswa];

        // 1. Saat siswa membuka kuis materi (play maupun result), sidebar HARUS mengaktifkan Pembelajaran, BUKAN Ujian Online
        $quizPlayRes = $this->withSession($session)->get(route('siswa.quiz.play', $this->quizMateri->id_quiz));
        $quizPlayContent = $quizPlayRes->getContent();

        // Pembelajaran harus memiliki class active-item
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="[^"]*\/siswa\/mapel"[^>]*class="[^"]*active-item[^"]*"[^>]*>[\s\S]*?Pembelajaran[\s\S]*?<\/a>/i',
            $quizPlayContent,
            'Menu Pembelajaran di sidebar harus aktif saat siswa mengerjakan kuis materi.'
        );

        // Ujian Online TIDAK BOLEH memiliki class active-item saat kuis
        $this->assertDoesNotMatchRegularExpression(
            '/<a[^>]*href="[^"]*\/siswa\/ujian"[^>]*class="[^"]*active-item[^"]*"[^>]*>[\s\S]*?Ujian Online[\s\S]*?<\/a>/i',
            $quizPlayContent,
            'Menu Ujian Online di sidebar tidak boleh aktif saat siswa mengerjakan kuis materi.'
        );

        // 2. Saat siswa membuka Ujian Online resmi, sidebar HARUS mengaktifkan Ujian Online, BUKAN Pembelajaran
        $examPlayRes = $this->withSession($session)->get(route('siswa.ujian.play', $this->ujianOnline->id_quiz));
        $examPlayContent = $examPlayRes->getContent();

        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="[^"]*\/siswa\/ujian"[^>]*class="[^"]*active-item[^"]*"[^>]*>[\s\S]*?Ujian Online[\s\S]*?<\/a>/i',
            $examPlayContent,
            'Menu Ujian Online di sidebar harus aktif saat siswa membuka ujian CBT online.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/<a[^>]*href="[^"]*\/siswa\/mapel"[^>]*class="[^"]*active-item[^"]*"[^>]*>[\s\S]*?Pembelajaran[\s\S]*?<\/a>/i',
            $examPlayContent,
            'Menu Pembelajaran di sidebar tidak boleh aktif saat siswa membuka ujian CBT online.'
        );
    }
}
