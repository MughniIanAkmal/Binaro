<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruUjianOnlineTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $mapel;
    protected $kelas;
    protected $siswa1;
    protected $siswa2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapel = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Matematika SD'],
            ['deskripsi' => 'Mapel Matematika']
        );

        $this->guru = Guru::firstOrCreate(
            ['nip' => '198501012010011099'],
            ['nama_guru' => 'Budi Santoso, S.Pd', 'email' => 'budi.santoso@sekolah.id', 'password' => 'password']
        );

        $this->kelas = Kelas::firstOrCreate(
            ['pararel' => 'Kelas 4-A'],
            ['tingkat' => 4]
        );

        $this->siswa1 = Siswa::create([
            'nm_siswa' => 'Ahmad Fauzi',
            'nisn' => '0012345678',
            'id_rooms' => $this->kelas->id_rooms,
            'jk' => 'L',
            'tgl_lahir' => '2012-05-10',
            'password' => 'password',
        ]);

        $this->siswa2 = Siswa::create([
            'nm_siswa' => 'Siti Nurhaliza',
            'nisn' => '0012345679',
            'id_rooms' => $this->kelas->id_rooms,
            'jk' => 'P',
            'tgl_lahir' => '2012-08-15',
            'password' => 'password',
        ]);
    }

    public function test_guru_can_view_ujian_index()
    {
        $response = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->get(route('guru.ujian.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Ujian Online');
        $response->assertSee('Buat Ujian Baru');
    }

    public function test_guru_can_view_create_page()
    {
        $response = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->get(route('guru.ujian.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Ujian Online Baru');
        $response->assertSee('Tingkatan Level Ujian');
        $response->assertSee('Level Mudah');
        $response->assertSee('Level Sedang');
        $response->assertSee('Level Susah');
        $response->assertSee('Target Peserta Ujian');
        $response->assertSee('Gambar Pendukung Soal');
    }

    public function test_guru_can_create_ujian_with_level_target_siswa_and_questions()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->create('diagram_lingkaran.png', 10, 'image/png');

        $payload = [
            'judul_quiz'    => 'Ujian Tengah Semester Ganjil',
            'id_mapel'      => $this->mapel->id_mapel,
            'tingkat_level' => 'susah',
            'durasi_menit'  => 90,
            'target_tipe'   => 'pilihan',
            'target_siswa'  => [$this->siswa1->id_siswa],
            'deskripsi'     => 'Kerjakan dengan teliti dan jujur.',
            'soal'          => [
                [
                    'pertanyaan'    => 'Berapakah luas lingkaran jika jari-jarinya 7 cm?',
                    'gambar'        => $image,
                    'opsi_a'        => '154 cm2',
                    'opsi_b'        => '144 cm2',
                    'opsi_c'        => '120 cm2',
                    'opsi_d'        => '110 cm2',
                    'kunci_jawaban' => 'A',
                    'bobot_nilai'   => 25,
                ]
            ]
        ];

        $response = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->post(route('guru.ujian.store'), $payload);

        $response->assertRedirect();

        $quiz = Quiz::where('judul_quiz', 'Ujian Tengah Semester Ganjil')->first();
        $this->assertNotNull($quiz);
        $this->assertEquals('susah', $quiz->tingkat_level);
        $this->assertEquals(90, $quiz->durasi_menit);
        $this->assertEquals('pilihan', $quiz->target_tipe);
        $this->assertTrue($quiz->targetSiswa->contains($this->siswa1));
        $this->assertFalse($quiz->targetSiswa->contains($this->siswa2));

        // Check soal
        $soal = SoalQuiz::where('id_quiz', $quiz->id_quiz)->first();
        $this->assertNotNull($soal);
        $this->assertEquals('Berapakah luas lingkaran jika jari-jarinya 7 cm?', $soal->pertanyaan);
        $this->assertEquals(25, $soal->bobot_nilai);
        $this->assertNotNull($soal->gambar);
        Storage::disk('public')->assertExists($soal->gambar);
    }

    public function test_guru_can_view_ujian_show_and_add_soal()
    {
        Storage::fake('public');

        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Kuis Cepat IPA',
            'tingkat_level' => 'mudah',
            'durasi_menit'  => 30,
            'target_tipe'   => 'semua',
        ]);

        $response = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->get(route('guru.ujian.show', $quiz->id_quiz));

        $response->assertStatus(200);
        $response->assertSee('Kuis Cepat IPA');
        $response->assertSee('Level Mudah');

        // Tambah soal baru lewat endpoint storeSoal
        $soalImage = UploadedFile::fake()->create('fotosintesis.jpg', 10, 'image/jpeg');
        $soalResponse = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->post(route('guru.ujian.soal.store', $quiz->id_quiz), [
                'pertanyaan'    => 'Apa zat hijau daun pada tumbuhan?',
                'gambar'        => $soalImage,
                'opsi_a'        => 'Klorofil',
                'opsi_b'        => 'Stomata',
                'opsi_c'        => 'Xilem',
                'opsi_d'        => 'Floem',
                'kunci_jawaban' => 'A',
                'bobot_nilai'   => 15,
            ]);

        $soalResponse->assertRedirect(route('guru.ujian.show', $quiz->id_quiz));

        $this->assertDatabaseHas('soal_quiz', [
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Apa zat hijau daun pada tumbuhan?',
            'kunci_jawaban' => 'A',
            'bobot_nilai'   => 15,
        ]);
    }

    public function test_guru_can_update_soal_and_quiz()
    {
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Kuis Sebelum Diedit',
            'tingkat_level' => 'sedang',
            'durasi_menit'  => 45,
            'target_tipe'   => 'semua',
        ]);

        $soal = SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Pertanyaan awal',
            'opsi_a'        => 'A',
            'opsi_b'        => 'B',
            'opsi_c'        => 'C',
            'opsi_d'        => 'D',
            'kunci_jawaban' => 'B',
            'bobot_nilai'   => 10,
        ]);

        // Update soal
        $response = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->put(route('guru.ujian.soal.update', [$quiz->id_quiz, $soal->id_soal]), [
                'pertanyaan'    => 'Pertanyaan sudah direvisi',
                'opsi_a'        => 'A revisi',
                'opsi_b'        => 'B revisi',
                'opsi_c'        => 'C revisi',
                'opsi_d'        => 'D revisi',
                'kunci_jawaban' => 'C',
                'bobot_nilai'   => 20,
            ]);

        $response->assertRedirect(route('guru.ujian.show', $quiz->id_quiz));

        $soal->refresh();
        $this->assertEquals('Pertanyaan sudah direvisi', $soal->pertanyaan);
        $this->assertEquals('C', $soal->kunci_jawaban);
        $this->assertEquals(20, $soal->bobot_nilai);

        // Update quiz
        $quizUpdateResponse = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->put(route('guru.ujian.update', $quiz->id_quiz), [
                'judul_quiz'    => 'Kuis Sesudah Diedit',
                'id_mapel'      => $this->mapel->id_mapel,
                'tingkat_level' => 'susah',
                'durasi_menit'  => 60,
                'target_tipe'   => 'semua',
            ]);

        $quizUpdateResponse->assertRedirect(route('guru.ujian.show', $quiz->id_quiz));
        $quiz->refresh();
        $this->assertEquals('Kuis Sesudah Diedit', $quiz->judul_quiz);
        $this->assertEquals('susah', $quiz->tingkat_level);
    }

    public function test_guru_can_delete_soal_and_quiz()
    {
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Kuis Akan Dihapus',
            'tingkat_level' => 'sedang',
            'durasi_menit'  => 40,
            'target_tipe'   => 'semua',
        ]);

        $soal = SoalQuiz::create([
            'id_quiz'       => $quiz->id_quiz,
            'pertanyaan'    => 'Soal akan dihapus',
            'opsi_a'        => 'A',
            'opsi_b'        => 'B',
            'opsi_c'        => 'C',
            'opsi_d'        => 'D',
            'kunci_jawaban' => 'A',
            'bobot_nilai'   => 10,
        ]);

        // Hapus soal
        $delSoalResponse = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->delete(route('guru.ujian.soal.destroy', [$quiz->id_quiz, $soal->id_soal]));

        $delSoalResponse->assertRedirect(route('guru.ujian.show', $quiz->id_quiz));
        $this->assertDatabaseMissing('soal_quiz', ['id_soal' => $soal->id_soal]);

        // Hapus quiz
        $delQuizResponse = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->delete(route('guru.ujian.destroy', $quiz->id_quiz));

        $delQuizResponse->assertRedirect(route('guru.ujian.index'));
        $this->assertDatabaseMissing('quiz', ['id_quiz' => $quiz->id_quiz]);
    }

    public function test_guru_can_create_and_update_soal_with_math_and_special_symbols()
    {
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Ujian Matematika & Simbol: 100% Valid!',
            'tingkat_level' => 'sedang',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);

        $pertanyaanWithSymbols = 'Jika (2x + 5) * 3 = 45, berapakah nilai x? Dan apakah x > 5 atau x <= 10?';
        $opsiA = 'x = 10 (100% benar)';
        $opsiB = 'x < 5 & x != 0';
        $opsiC = 'x + 2 = 12 / 2';
        $opsiD = 'x = 0; "tidak terdefinisi"';

        // Add soal via POST route guru.ujian.soal.store
        $response = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->post(route('guru.ujian.soal.store', $quiz->id_quiz), [
                'pertanyaan'    => $pertanyaanWithSymbols,
                'opsi_a'        => $opsiA,
                'opsi_b'        => $opsiB,
                'opsi_c'        => $opsiC,
                'opsi_d'        => $opsiD,
                'kunci_jawaban' => 'A',
                'bobot_nilai'   => 15,
            ]);

        $response->assertRedirect(route('guru.ujian.show', $quiz->id_quiz));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('soal_quiz', [
            'id_quiz'    => $quiz->id_quiz,
            'pertanyaan' => $pertanyaanWithSymbols,
            'opsi_a'     => $opsiA,
            'opsi_b'     => $opsiB,
            'opsi_c'     => $opsiC,
            'opsi_d'     => $opsiD,
        ]);

        $soal = SoalQuiz::where('id_quiz', $quiz->id_quiz)->first();

        // Update soal with more complex symbols
        $updatedPertanyaan = 'Berapakah hasil dari 25% * 400 + (10 - 2) / 2 = ...?';
        $updateResponse = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->put(route('guru.ujian.soal.update', [$quiz->id_quiz, $soal->id_soal]), [
                'pertanyaan'    => $updatedPertanyaan,
                'opsi_a'        => '104 (x = 104)',
                'opsi_b'        => '96 (x < 100)',
                'opsi_c'        => '100 (x = 100)',
                'opsi_d'        => '110 (x > 105)',
                'kunci_jawaban' => 'A',
                'bobot_nilai'   => 20,
            ]);

        $updateResponse->assertRedirect(route('guru.ujian.show', $quiz->id_quiz));
        $soal->refresh();
        $this->assertEquals($updatedPertanyaan, $soal->pertanyaan);
        $this->assertEquals('104 (x = 104)', $soal->opsi_a);
    }

    public function test_guru_cannot_input_numbers_in_judul_quiz()
    {
        $session = ['user_type' => 'guru', 'user_id' => $this->guru->id_guru];

        // 1. Judul mengandung angka -> ditolak
        $resNumber = $this->withSession($session)->post(route('guru.ujian.store'), [
            'judul_quiz'    => 'Ujian Tengah Semester 1',
            'id_mapel'      => $this->mapel->id_mapel,
            'tingkat_level' => 'mudah',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);
        $resNumber->assertSessionHasErrors(['judul_quiz']);

        // 2. Judul mengandung angka lain -> ditolak
        $resNumber2 = $this->withSession($session)->post(route('guru.ujian.store'), [
            'judul_quiz'    => 'Ulangan Harian Matematika Kelas 4',
            'id_mapel'      => $this->mapel->id_mapel,
            'tingkat_level' => 'mudah',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);
        $resNumber2->assertSessionHasErrors(['judul_quiz']);

        // 3. Judul tanpa angka (hanya huruf dan spasi) -> sukses
        $resValid = $this->withSession($session)->post(route('guru.ujian.store'), [
            'judul_quiz'    => 'Ulangan Harian Matematika Tingkat Empat',
            'id_mapel'      => $this->mapel->id_mapel,
            'tingkat_level' => 'mudah',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);
        $resValid->assertRedirect();
        $this->assertDatabaseHas('quiz', [
            'judul_quiz' => 'Ulangan Harian Matematika Tingkat Empat',
        ]);

        $quiz = Quiz::where('judul_quiz', 'Ulangan Harian Matematika Tingkat Empat')->first();

        // 4. Update dengan angka -> ditolak
        $resUpdateNumber = $this->withSession($session)->put(route('guru.ujian.update', $quiz->id_quiz), [
            'judul_quiz'    => 'Ulangan Harian Matematika Revisi 2',
            'id_mapel'      => $this->mapel->id_mapel,
            'tingkat_level' => 'mudah',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);
        $resUpdateNumber->assertSessionHasErrors(['judul_quiz']);

        // 5. Update tanpa angka -> sukses
        $resUpdateValid = $this->withSession($session)->put(route('guru.ujian.update', $quiz->id_quiz), [
            'judul_quiz'    => 'Ulangan Harian Matematika Revisi Dua',
            'id_mapel'      => $this->mapel->id_mapel,
            'tingkat_level' => 'mudah',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);
        $resUpdateValid->assertRedirect();
        $quiz->refresh();
        $this->assertEquals('Ulangan Harian Matematika Revisi Dua', $quiz->judul_quiz);
    }

    public function test_ujian_views_render_time_per_question_calculator()
    {
        $session = ['user_type' => 'guru', 'user_id' => $this->guru->id_guru];

        // 1. Create page renders calculation widget
        $createRes = $this->withSession($session)->get(route('guru.ujian.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('Kalkulasi Otomatis Alokasi Waktu per Soal');
        $createRes->assertSee('calc-input-soal');
        $createRes->assertSee('result-waktu-per-soal');

        // 2. Show page renders time per question
        $quiz = Quiz::create([
            'id_guru'       => $this->guru->id_guru,
            'id_mapel'      => $this->mapel->id_mapel,
            'judul_quiz'    => 'Ujian Penilaian Harian Sains',
            'tingkat_level' => 'mudah',
            'durasi_menit'  => 60,
            'target_tipe'   => 'semua',
        ]);

        for ($i = 1; $i <= 20; $i++) {
            SoalQuiz::create([
                'id_quiz' => $quiz->id_quiz,
                'pertanyaan' => 'Pertanyaan nomor ' . $i,
                'opsi_a' => 'A',
                'opsi_b' => 'B',
                'opsi_c' => 'C',
                'opsi_d' => 'D',
                'kunci_jawaban' => 'A',
                'bobot_nilai' => 5,
            ]);
        }

        $showRes = $this->withSession($session)->get(route('guru.ujian.show', $quiz->id_quiz));
        $showRes->assertStatus(200);
        $showRes->assertSee('Durasi & Waktu Per Soal', false);
        // 60 minutes / 20 questions = 3 minutes per question
        $showRes->assertSee('Rata-rata: 3m', false);
    }
}
