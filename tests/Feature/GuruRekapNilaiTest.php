<?php

namespace Tests\Feature;

use App\Models\Bab;
use App\Models\Guru;
use App\Models\HasilKuisSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SubBab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruRekapNilaiTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $guruUser;
    protected $kelas;
    protected $mapel;
    protected $bab;
    protected $subBab;
    protected $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Guru::create([
            'nip'       => '198501012010012001',
            'nama_guru' => 'Ibu Siti Aminah, S.Pd',
            'password'  => 'guru123',
        ]);

        $this->mapel = MataPelajaran::create([
            'nama_mapel' => 'Matematika',
            'deskripsi'  => 'Matematika SD',
        ]);

        $this->kelas = Kelas::create([
            'pararel' => 'Kelas 5-A',
            'tingkat' => 5,
        ]);

        $this->bab = Bab::create([
            'id_mapel' => $this->mapel->id_mapel,
            'nama_bab' => 'Pecahan',
            'urutan'   => 1,
        ]);

        $this->subBab = SubBab::create([
            'id_bab'       => $this->bab->id_bab,
            'nama_sub_bab' => 'Penjumlahan Pecahan',
        ]);

        $this->siswa = Siswa::create([
            'nm_siswa' => 'Budi Pratama',
            'nisn'     => '1234567890',
            'id_rooms' => $this->kelas->id_rooms,
            'password' => 'siswa123',
        ]);
    }

    public function test_sidebar_displays_rekap_nilai_link()
    {
        $session = [
            'user_id'   => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ];

        $response = $this->withSession($session)->get(route('rekap_nilai.index'));
        $response->assertOk();
        $response->assertSee('Rekap Nilai');
    }

    public function test_quiz_mode_only_shows_view_and_print_no_remedial_and_no_kirim_nilai()
    {
        $session = [
            'user_id'   => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ];

        // Create Quiz (has id_sub_bab)
        $quiz = Quiz::create([
            'id_guru'      => $this->guru->id_guru,
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_quiz'   => 'Kuis Cepat Pecahan',
            'durasi_menit' => 15,
        ]);

        HasilKuisSiswa::create([
            'id_quiz'      => $quiz->id_quiz,
            'id_siswa'     => $this->siswa->id_siswa,
            'nilai_akhir'  => 55, // below KKM
            'jumlah_benar' => 5,
            'jumlah_salah' => 5,
            'waktu_menit'  => 10,
        ]);

        $response = $this->withSession($session)->get(route('rekap_nilai.index', ['quiz_id' => $quiz->id_quiz]));

        $response->assertOk();
        // Quiz must have "Cetak Daftar Nilai"
        $response->assertSee('Cetak Daftar Nilai');
        // Quiz must NOT have "Cetak Rekap Nilai" (that is for Ujian)
        $response->assertDontSee('Cetak Rekap Nilai');
        // Quiz must NOT have "Kirim Nilai ke Semua Siswa"
        $response->assertDontSee('Kirim Nilai ke Semua Siswa');
        // Quiz must NOT have Remedial Tab
        $response->assertDontSee('id="tab-remedial"', false);
        // Quiz must NOT have Remedial banner
        $response->assertDontSee('Jadwalkan Remedial Otomatis');

        // Backend guard: attempting to schedule remedial on a Quiz should be blocked
        $remedialRes = $this->withSession($session)->post(route('rekap_nilai.jadwalkan_remedial', $quiz->id_quiz));
        $remedialRes->assertSessionHas('error');

        // Backend guard: attempting to send score on a Quiz should be blocked
        $kirimSemuaRes = $this->withSession($session)->post(route('rekap_nilai.kirim_semua', $quiz->id_quiz));
        $kirimSemuaRes->assertSessionHas('error');
    }

    public function test_ujian_mode_shows_remedial_kirim_nilai_and_cetak_rekap_nilai()
    {
        $session = [
            'user_id'   => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ];

        // Create Ujian (no id_sub_bab, has id_mapel)
        $ujian = Quiz::create([
            'id_guru'      => $this->guru->id_guru,
            'id_mapel'     => $this->mapel->id_mapel,
            'judul_quiz'   => 'Ujian Tengah Semester MTK',
            'durasi_menit' => 60,
        ]);

        HasilKuisSiswa::create([
            'id_quiz'      => $ujian->id_quiz,
            'id_siswa'     => $this->siswa->id_siswa,
            'nilai_akhir'  => 40, // below KKM
            'jumlah_benar' => 4,
            'jumlah_salah' => 6,
            'waktu_menit'  => 45,
            'status_kirim' => 0,
        ]);

        $response = $this->withSession($session)->get(route('rekap_nilai.index', ['quiz_id' => $ujian->id_quiz]));

        $response->assertOk();
        // Ujian must have "Cetak Rekap Nilai"
        $response->assertSee('Cetak Rekap Nilai');
        // Ujian must have "Kirim Nilai ke Semua Siswa"
        // Ujian must have Remedial Tab
        $response->assertSee('id="tab-remedial"', false);
        // Ujian must have Remedial banner
        $response->assertSee('Jadwalkan Remedial Otomatis');
        // Ujian must display Durasi Waktu Pengerjaan
        $response->assertSee('Durasi Waktu Pengerjaan');
        $response->assertSee('45 menit');

        // Backend allow: kirim semua for Ujian
        $kirimSemuaRes = $this->withSession($session)->post(route('rekap_nilai.kirim_semua', $ujian->id_quiz));
        $kirimSemuaRes->assertSessionHas('success');

        // Backend allow: jadwalkan remedial for Ujian
        $remedialRes = $this->withSession($session)->post(route('rekap_nilai.jadwalkan_remedial', $ujian->id_quiz));
        $remedialRes->assertSessionHas('success');
    }
}
