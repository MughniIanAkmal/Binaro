<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Bab;
use App\Models\Guru;
use App\Models\HasilKuisSiswa;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use App\Models\SubBab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class BinaroLearningPrdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('test');
    }

    public function test_smart_auth_detects_siswa_with_10_digits()
    {
        $siswa = Siswa::create([
            'nm_siswa' => 'Budi Santoso',
            'nisn' => '1234567890', // exactly 10 digits
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'username' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/siswa/dashboard');
        $this->assertEquals('siswa', session('user_type'));
        $this->assertEquals($siswa->id_siswa, session('user_id'));
    }

    public function test_smart_auth_detects_guru_with_18_digits()
    {
        $guru = Guru::create([
            'nama_guru' => 'Bapak Ahmad',
            'nip' => '198501012010011001', // exactly 18 digits
            'password' => 'guru12345',
        ]);

        $response = $this->post('/login', [
            'username' => '198501012010011001',
            'password' => 'guru12345',
        ]);

        $response->assertRedirect('/guru/dashboard');
        $this->assertEquals('guru', session('user_type'));
        $this->assertEquals($guru->id_guru, session('user_id'));
    }

    public function test_anti_bruteforce_locks_after_5_attempts()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'wronguser123',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be blocked
        $response = $this->post('/login', [
            'username' => 'wronguser123',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('error'));
    }

    public function test_guru_learning_hierarchy_navigation_and_cascading()
    {
        $guru = Guru::create([
            'nama_guru' => 'Ibu Guru',
            'nip' => '198501012010011002',
            'password' => 'pass123',
        ]);

        $mapel = MataPelajaran::create([
            'nama_mapel' => 'Matematika SD',
            'deskripsi' => 'Belajar matematika dasar',
        ]);

        $bab = Bab::create([
            'id_mapel' => $mapel->id_mapel,
            'nama_bab' => 'Bab 1: Penjumlahan',
        ]);

        $subBab = SubBab::create([
            'id_bab' => $bab->id_bab,
            'nama_sub_bab' => 'Sub-Bab 1.1: Bilangan Cacah',
        ]);

        $materiVideo = Materi::create([
            'id_bab' => $bab->id_bab,
            'id_sub_bab' => $subBab->id_sub_bab,
            'judul_materi' => 'Video Berhitung',
            'tipe_materi' => 'video',
            'url_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $materiDoc = Materi::create([
            'id_bab' => $bab->id_bab,
            'id_sub_bab' => $subBab->id_sub_bab,
            'judul_materi' => 'Modul PDF Hitung',
            'tipe_materi' => 'dokumen',
            'file_pdf' => 'materi_pdf/test.pdf',
        ]);

        $quiz = Quiz::create([
            'id_sub_bab' => $subBab->id_sub_bab,
            'judul_quiz' => 'Kuis Bilangan',
        ]);

        $materiQuiz = Materi::create([
            'id_bab' => $bab->id_bab,
            'id_sub_bab' => $subBab->id_sub_bab,
            'judul_materi' => 'Kuis Evaluasi 1',
            'tipe_materi' => 'kuis',
            'id_quiz' => $quiz->id_quiz,
        ]);

        $session = ['user_id' => $guru->id_guru, 'user_type' => 'guru', 'user_name' => $guru->nama_guru];

        // 1. Grid Mapel
        $this->withSession($session)
             ->get('/guru/mapel-belajar')
             ->assertOk()
             ->assertSee('Matematika SD');

        // 2. List Bab
        $this->withSession($session)
             ->get("/guru/mapel-belajar/{$mapel->id_mapel}/bab")
             ->assertOk()
             ->assertSee('Bab 1: Penjumlahan');

        // 3. List Sub-Bab
        $this->withSession($session)
             ->get("/guru/bab/{$bab->id_bab}/sub-bab")
             ->assertOk()
             ->assertSee('Sub-Bab 1.1: Bilangan Cacah');

        // 4. List Materi (3 Tipe)
        $this->withSession($session)
             ->get("/guru/sub-bab/{$subBab->id_sub_bab}/materi")
             ->assertOk()
             ->assertSee('Video Berhitung')
             ->assertSee('Modul PDF Hitung')
             ->assertSee('Kuis Evaluasi 1');

        // 5. Cascading Dropdown APIs
        $this->withSession($session)
             ->getJson("/guru/api/cascading/babs/{$mapel->id_mapel}")
             ->assertOk()
             ->assertJsonFragment(['id_bab' => $bab->id_bab, 'nama_bab' => 'Bab 1: Penjumlahan']);

        $this->withSession($session)
             ->getJson("/guru/api/cascading/sub-babs/{$bab->id_bab}")
             ->assertOk()
             ->assertJsonFragment(['id_sub_bab' => $subBab->id_sub_bab, 'nama_sub_bab' => 'Sub-Bab 1.1: Bilangan Cacah']);

        $this->withSession($session)
             ->getJson("/guru/api/cascading/materis/{$subBab->id_sub_bab}")
             ->assertOk()
             ->assertJsonFragment(['id_materi' => $materiVideo->id_materi]);

        $this->withSession($session)
             ->getJson("/guru/api/cascading/materi/{$materiVideo->id_materi}")
             ->assertOk()
             ->assertJsonFragment(['judul_materi' => 'Video Berhitung']);
    }

    public function test_guardrail_anti_duplicate_bab_and_subbab()
    {
        $guru = Guru::create([
            'nama_guru' => 'Ibu Guru',
            'nip' => '198501012010011003',
            'password' => 'pass123',
        ]);

        $mapel = MataPelajaran::create(['nama_mapel' => 'IPA']);
        $bab = Bab::create(['id_mapel' => $mapel->id_mapel, 'nama_bab' => 'Tata Surya']);

        $session = ['user_id' => $guru->id_guru, 'user_type' => 'guru'];

        // Try adding duplicate Bab
        $response = $this->withSession($session)->post('/guru/materi/bab', [
            'id_mapel' => $mapel->id_mapel,
            'nama_bab' => 'Tata Surya',
        ]);
        $response->assertSessionHas('error');

        // Try adding duplicate Sub-Bab
        SubBab::create(['id_bab' => $bab->id_bab, 'nama_sub_bab' => 'Planet Mars']);
        $responseSub = $this->withSession($session)->post('/guru/materi/sub-bab', [
            'id_bab' => $bab->id_bab,
            'nama_sub_bab' => 'Planet Mars',
        ]);
        $responseSub->assertSessionHas('error');
    }

    public function test_siswa_subbab_list_then_materi_page_flow()
    {
        $mapel = MataPelajaran::create(['nama_mapel' => 'Matematika Dasar']);
        $bab = Bab::create(['id_mapel' => $mapel->id_mapel, 'nama_bab' => 'Bab Pecahan']);
        $subBab = SubBab::create(['id_bab' => $bab->id_bab, 'nama_sub_bab' => 'Sub Pecahan Senilai']);
        $materi = Materi::create([
            'id_bab' => $bab->id_bab,
            'id_sub_bab' => $subBab->id_sub_bab,
            'judul_materi' => 'Video Pecahan Senilai',
            'tipe_materi' => 'video',
            'url_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $siswa = Siswa::create(['nm_siswa' => 'Rina', 'nisn' => '1234512345', 'password' => '123']);
        $session = ['user_id' => $siswa->id_siswa, 'user_type' => 'siswa'];

        // 1. Siswa opens Mapel -> sees Bab and Sub-Bab list
        $this->withSession($session)
             ->get("/siswa/mapel/{$mapel->id_mapel}")
             ->assertOk()
             ->assertSee('Sub Pecahan Senilai')
             ->assertSee('Buka Materi');

        // 2. Siswa clicks Sub-Bab -> enters dedicated Materi page
        $this->withSession($session)
             ->get("/siswa/sub-bab/{$subBab->id_sub_bab}/materi")
             ->assertOk()
             ->assertSee('Video Pecahan Senilai');

        // 3. Siswa opens the specific materi viewer
        $this->withSession($session)
             ->get("/siswa/materi/{$materi->id_materi}")
             ->assertOk()
             ->assertSee('Video Pecahan Senilai');
    }

    public function test_siswa_quiz_randomization_auto_grading_and_session_lock()
    {
        $mapel = MataPelajaran::create(['nama_mapel' => 'Bahasa Indonesia']);
        $bab = Bab::create(['id_mapel' => $mapel->id_mapel, 'nama_bab' => 'Membaca']);
        $subBab = SubBab::create(['id_bab' => $bab->id_bab, 'nama_sub_bab' => 'Puisi']);
        $quiz = Quiz::create(['id_sub_bab' => $subBab->id_sub_bab, 'judul_quiz' => 'Kuis Puisi']);

        // Create 8 questions in question bank
        for ($i = 1; $i <= 8; $i++) {
            SoalQuiz::create([
                'id_quiz' => $quiz->id_quiz,
                'pertanyaan' => "Pertanyaan ke-{$i}?",
                'opsi_a' => 'Opsi A',
                'opsi_b' => 'Opsi B',
                'opsi_c' => 'Opsi C',
                'opsi_d' => 'Opsi D',
                'kunci_jawaban' => 'B',
            ]);
        }

        $siswa = Siswa::create([
            'nm_siswa' => 'Citra Lestari',
            'nisn' => '9988776655',
            'password' => 'siswa123',
        ]);

        $session = ['user_id' => $siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $siswa->nm_siswa];

        // 1. Siswa plays quiz -> receives exactly 5 questions
        $playRes = $this->withSession($session)->get("/siswa/quiz/{$quiz->id_quiz}/play");
        $playRes->assertOk();
        $playRes->assertViewHas('soals', function ($soals) {
            return count($soals) === 5;
        });

        // 2. Submit quiz: answer 4 correctly (B) and 1 wrong (A)
        $soals = SoalQuiz::where('id_quiz', $quiz->id_quiz)->take(5)->get();
        $jawaban = [
            $soals[0]->id_soal => 'B', // benar
            $soals[1]->id_soal => 'B', // benar
            $soals[2]->id_soal => 'B', // benar
            $soals[3]->id_soal => 'B', // benar
            $soals[4]->id_soal => 'A', // salah
        ];

        $submitRes = $this->withSession($session)->post("/siswa/quiz/{$quiz->id_quiz}/submit", [
            'jawaban' => $jawaban,
        ]);

        $submitRes->assertRedirect("/siswa/quiz/{$quiz->id_quiz}/result");

        // Verify HasilKuisSiswa record
        $hasil = HasilKuisSiswa::where('id_quiz', $quiz->id_quiz)
                               ->where('id_siswa', $siswa->id_siswa)
                               ->first();

        $this->assertNotNull($hasil);
        $this->assertEquals(4, $hasil->jumlah_benar);
        $this->assertEquals(1, $hasil->jumlah_salah);
        $this->assertEquals(80.00, (float)$hasil->nilai_akhir);

        // 3. Result page renders score and review
        $resultRes = $this->withSession($session)->get("/siswa/quiz/{$quiz->id_quiz}/result");
        $resultRes->assertOk();
        $resultRes->assertSee('80');
        $resultRes->assertSee('Jawaban Benar');

        // 4. Session Lock Guardrail: Student cannot play or submit again
        $playAgain = $this->withSession($session)->get("/siswa/quiz/{$quiz->id_quiz}/play");
        $playAgain->assertRedirect("/siswa/quiz/{$quiz->id_quiz}/result");
    }

    public function test_guru_quiz_rekap_and_export_csv()
    {
        $guru = Guru::create([
            'nama_guru' => 'Guru Penilai',
            'nip' => '198501012010011004',
            'password' => 'pass123',
        ]);

        $mapel = MataPelajaran::create(['nama_mapel' => 'IPA Terpadu']);
        $bab = Bab::create(['id_mapel' => $mapel->id_mapel, 'nama_bab' => 'Bab Gaya']);
        $subBab = SubBab::create(['id_bab' => $bab->id_bab, 'nama_sub_bab' => 'Sub Gaya Gesek']);
        $quiz = Quiz::create(['id_sub_bab' => $subBab->id_sub_bab, 'judul_quiz' => 'Kuis Gaya']);

        $siswa = Siswa::create(['nm_siswa' => 'Doni', 'nisn' => '1122334455', 'password' => '123']);
        HasilKuisSiswa::create([
            'id_quiz' => $quiz->id_quiz,
            'id_siswa' => $siswa->id_siswa,
            'jumlah_benar' => 5,
            'jumlah_salah' => 0,
            'nilai_akhir' => 100.00,
        ]);

        $session = ['user_id' => $guru->id_guru, 'user_type' => 'guru'];

        // Rekap view
        $this->withSession($session)
             ->get('/guru/quiz/rekap?quiz_id=' . $quiz->id_quiz)
             ->assertOk()
             ->assertSee('Doni')
             ->assertSee('100');

        // Export stream
        $exportRes = $this->withSession($session)->get("/guru/quiz/export/{$quiz->id_quiz}");
        $exportRes->assertOk();
        $this->assertStringContainsString('text/csv', $exportRes->headers->get('content-type'));
    }
}
