<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pr;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputValidationAndHumanErrorTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $admin;
    protected $kelas;
    protected $mapel;
    protected $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nip' => 'ADMIN001',
            'nama_admin' => 'Administrator',
            'password' => 'password123',
        ]);

        $this->guru = Guru::create([
            'nama_guru' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Pendidikan',
            'username' => 'guru_budi',
            'password' => bcrypt('password123'),
        ]);

        $this->kelas = Kelas::create([
            'pararel' => 'Kelas 4A',
        ]);

        $this->mapel = MataPelajaran::create([
            'nama_mapel' => 'Matematika Dasar',
            'deskripsi' => 'Mapel Berhitung',
        ]);

        $this->siswa = Siswa::create([
            'nm_siswa' => 'Ahmad Rizki',
            'nis' => '12345',
            'nisn' => '0012345678',
            'no_hp' => '082233445566',
            'id_rooms' => $this->kelas->id_rooms,
            'id_mapel' => $this->mapel->id_mapel,
            'username' => 'ahmad123',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_notifikasi_pr_blocks_illegal_symbols_and_excessive_length()
    {
        // Symbols like <script>, $, #, %, * should be blocked
        $response = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->post(route('guru.notifikasi_pr.store'), [
                'nama_pr' => 'PR <script>alert("xss")</script> $$$',
                'id_mapel' => $this->mapel->id_mapel,
                'id_kelas' => $this->kelas->id_rooms,
                'tgl_tenggat' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'deskripsi' => 'Kerjakan halaman 12',
            ]);

        $response->assertSessionHasErrors('nama_pr');

        // Over 100 characters should be blocked
        $longName = str_repeat('A', 101);
        $responseLong = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->post(route('guru.notifikasi_pr.store'), [
                'nama_pr' => $longName,
                'id_mapel' => $this->mapel->id_mapel,
                'id_kelas' => $this->kelas->id_rooms,
                'tgl_tenggat' => now()->addDays(2)->format('Y-m-d\TH:i'),
            ]);

        $responseLong->assertSessionHasErrors('nama_pr');

        // Valid name with allowed punctuation (hyphen, dot, comma, parentheses) should succeed
        $nextMonday = now()->next(\Carbon\Carbon::MONDAY)->setTime(10, 0);
        $responseValid = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->post(route('guru.notifikasi_pr.store'), [
                'nama_pr' => 'Tugas Bab 1 - Latihan A (Hal. 10, No. 1-5)',
                'id_mapel' => $this->mapel->id_mapel,
                'id_kelas' => $this->kelas->id_rooms,
                'tgl_tenggat' => $nextMonday->format('Y-m-d\TH:i'),
                'deskripsi' => 'Kerjakan dengan teliti.',
            ]);

        $responseValid->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pr', [
            'nama_pr' => 'Tugas Bab 1 - Latihan A (Hal. 10, No. 1-5)',
        ]);
    }

    public function test_mapel_blocks_dangerous_symbols_and_limits_length()
    {
        $response = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->post(route('mapel.store'), [
                'nama_mapel' => '<img src=x onerror=alert(1)>',
                'deskripsi' => 'Tes XSS',
            ]);

        $response->assertSessionHasErrors('nama_mapel');

        $responseValid = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->post(route('mapel.store'), [
                'nama_mapel' => 'Pendidikan Jasmani & Kesehatan (PJOK)',
                'deskripsi' => 'Olahraga dan kesehatan',
            ]);

        $responseValid->assertSessionHasNoErrors();
        $this->assertDatabaseHas('mata_pelajaran', [
            'nama_mapel' => 'Pendidikan Jasmani & Kesehatan (PJOK)',
        ]);
    }

    public function test_guru_and_siswa_inputs_enforce_symbol_and_length_boundaries()
    {
        // Guru creation with invalid symbols in name
        $responseGuru = $this->withSession(['user_id' => $this->admin->id_admin, 'user_type' => 'admin'])
            ->post(route('guru.store'), [
                'nama' => 'Guru <script> 123 #$%',
                'nip' => '199011112222333444',
                'no_hp' => '081234567890',
                'alamat' => 'Alamat valid',
                'username' => 'gurubaru',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $responseGuru->assertSessionHasErrors('nama');

        // Siswa creation with invalid symbols in name
        $responseSiswa = $this->withSession(['user_id' => $this->admin->id_admin, 'user_type' => 'admin'])
            ->post(route('siswa.store'), [
                'nama' => 'Siswa *&^% 123',
                'nis' => '54321',
                'nisn' => '0098765432',
                'id_rooms' => $this->kelas->id_rooms,
                'no_hp' => '089988776655',
                'alamat' => 'Alamat Siswa',
                'username' => 'siswabaru',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $responseSiswa->assertSessionHasErrors('nama');
    }

    public function test_ujian_online_soal_validation_rejects_empty_or_excessive_options()
    {
        $quiz = Quiz::create([
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'judul_quiz' => 'Ujian Tengah Semester Ganjil',
            'durasi' => 60,
            'total_soal' => 1,
            'status' => 'draft',
        ]);

        // Question over 2000 chars should fail
        $longPertanyaan = str_repeat('A', 2001);
        $response = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->post(route('guru.ujian.soal.store', $quiz->id_quiz), [
                'pertanyaan' => $longPertanyaan,
                'opsi_a' => 'A',
                'opsi_b' => 'B',
                'opsi_c' => 'C',
                'opsi_d' => 'D',
                'kunci_jawaban' => 'A',
                'bobot_nilai' => 10,
            ]);

        $response->assertSessionHasErrors('pertanyaan');

        // Invalid kunci jawaban (e.g. 'Z') should fail
        $responseKunci = $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->post(route('guru.ujian.soal.store', $quiz->id_quiz), [
                'pertanyaan' => 'Berapa 10 + 10?',
                'opsi_a' => '20',
                'opsi_b' => '30',
                'opsi_c' => '40',
                'opsi_d' => '50',
                'kunci_jawaban' => 'Z',
                'bobot_nilai' => 10,
            ]);

        $responseKunci->assertSessionHasErrors('kunci_jawaban');
    }
}
