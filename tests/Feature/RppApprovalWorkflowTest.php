<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RppApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $admin;
    protected $mapel;
    protected $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Guru::firstOrCreate(
            ['nip' => '198801012015011001'],
            ['nama_guru' => 'Siti Aminah, M.Pd', 'email' => 'siti@binaro.sch.id', 'password' => 'password']
        );

        $this->admin = Admin::firstOrCreate(
            ['username' => 'admin_test'],
            ['nip' => '198001012005011001', 'nama_admin' => 'Admin Utama', 'password' => 'password', 'role' => 'admin']
        );

        $this->mapel = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Bahasa Indonesia'],
            ['deskripsi' => 'Mapel Bahasa Indonesia']
        );

        $this->kelas = Kelas::firstOrCreate(
            ['pararel' => 'Kelas 4-A'],
            ['tingkat' => 4]
        );
    }

    public function test_guru_stores_rpp_with_publish_action_sets_status_to_menunggu_review_not_terverifikasi()
    {
        $guruSession = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];

        $payload = [
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Menulis Surat Resmi dan Surel',
            'fase' => 'Fase B',
            'modul_ke' => 'Modul 3',
            'alokasi_waktu' => '2 JP',
            'deskripsi' => 'Peserta didik memahami struktur penulisan surat.',
            'action' => 'publish',
            // Attempt to bypass by injecting status
            'status' => 'terverifikasi',
        ];

        $response = $this->withSession($guruSession)
            ->post(route('guru.rpp.store'), $payload);

        $response->assertRedirect(route('guru.rpp.index'));
        $response->assertSessionHas('success');

        $rpp = Rpp::where('judul_rpp', 'Menulis Surat Resmi dan Surel')->first();
        $this->assertNotNull($rpp);
        // It must NOT be immediately approved/terverifikasi!
        $this->assertEquals('menunggu_review', $rpp->status);
    }

    public function test_guru_stores_rpp_as_draft()
    {
        $guruSession = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];

        $payload = [
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Draf Konsep Puisi Anak',
            'action' => 'draft',
        ];

        $response = $this->withSession($guruSession)
            ->post(route('guru.rpp.store'), $payload);

        $response->assertRedirect(route('guru.rpp.index'));

        $rpp = Rpp::where('judul_rpp', 'Draf Konsep Puisi Anak')->first();
        $this->assertNotNull($rpp);
        $this->assertEquals('draft', $rpp->status);
    }

    public function test_guru_updating_rpp_and_submitting_goes_to_menunggu_review()
    {
        $guruSession = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];

        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Membaca Nyaring',
            'status' => 'draft',
        ]);

        $updatePayload = [
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Membaca Nyaring Revisi 1',
            'action' => 'publish',
        ];

        $response = $this->withSession($guruSession)
            ->put(route('guru.rpp.update', $rpp->id_rpp), $updatePayload);

        $response->assertRedirect(route('guru.rpp.index'));
        $rpp->refresh();
        $this->assertEquals('menunggu_review', $rpp->status);
    }

    public function test_guru_index_view_shows_waiting_approval_and_locks_teaching_button()
    {
        $guruSession = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];

        $rppPending = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Modul Menunggu Review Admin',
            'status' => 'menunggu_review',
        ]);

        $response = $this->withSession($guruSession)
            ->get(route('guru.rpp.index'));

        $response->assertStatus(200);
        $response->assertSee('Menunggu Persetujuan Admin');
        $response->assertSee('Menunggu di-Accept Admin');
        // Check that "Mulai Mengajar" is NOT shown for this pending RPP
        $response->assertDontSee('Mulai Mengajar');
    }

    public function test_admin_accepts_rpp_and_enables_mulai_mengajar_for_guru()
    {
        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Tata Bahasa Baku',
            'status' => 'menunggu_review',
        ]);

        // Admin approves RPP
        $adminSession = ['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nm_admin];

        $approvalResponse = $this->withSession($adminSession)
            ->patch(route('rpp.updateStatus', $rpp->id_rpp), [
                'status' => 'terverifikasi',
                'catatan_revisi' => null,
            ]);

        $approvalResponse->assertRedirect();
        $rpp->refresh();
        $this->assertEquals('terverifikasi', $rpp->status);

        // Guru views index: now the RPP is approved and "Mulai Mengajar" is accessible
        $guruSession = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];
        $guruResponse = $this->withSession($guruSession)
            ->get(route('guru.rpp.index'));

        $guruResponse->assertStatus(200);
        $guruResponse->assertSee('Disetujui Admin / Siap Ajar');
        $guruResponse->assertSee('Mulai Mengajar');
    }

    public function test_admin_can_request_revision_and_guru_sees_notes()
    {
        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Puisi Kontemporer',
            'status' => 'menunggu_review',
        ]);

        $adminSession = ['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nm_admin];

        $revisionResponse = $this->withSession($adminSession)
            ->patch(route('rpp.updateStatus', $rpp->id_rpp), [
                'status' => 'perlu_revisi',
                'catatan_revisi' => 'Harap lengkapi rubrik penilaian sikap peserta didik.',
            ]);

        $revisionResponse->assertRedirect();
        $rpp->refresh();
        $this->assertEquals('perlu_revisi', $rpp->status);
        $this->assertEquals('Harap lengkapi rubrik penilaian sikap peserta didik.', $rpp->catatan_revisi);

        // Guru index should show "Perlu Revisi"
        $guruSession = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];
        $guruResponse = $this->withSession($guruSession)
            ->get(route('guru.rpp.index'));

        $guruResponse->assertStatus(200);
        $guruResponse->assertSee('Perlu Revisi');
        $guruResponse->assertSee('Harap lengkapi rubrik penilaian sikap peserta didik.');
    }

    public function test_admin_approving_or_revising_rpp_does_not_create_siswa_notifikasi_pr()
    {
        $siswa = \App\Models\Siswa::create([
            'nm_siswa' => 'Budi Santoso',
            'nis' => '12345',
            'nisn' => '0098765432',
            'id_rooms' => $this->kelas->id_rooms,
            'id_mapel' => $this->mapel->id_mapel,
            'username' => 'budi123',
            'password' => 'password123',
        ]);

        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Modul Pembelajaran Tata Surya',
            'status' => 'menunggu_review',
        ]);

        $adminSession = ['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nm_admin];

        // Admin approves RPP
        $this->withSession($adminSession)
            ->patch(route('rpp.updateStatus', $rpp->id_rpp), [
                'status' => 'terverifikasi',
                'catatan_revisi' => null,
            ]);

        // Assert no notification was sent to siswa
        $this->assertDatabaseMissing('notifikasi', [
            'id_siswa' => $siswa->id_siswa,
        ]);

        // Siswa visits notifikasi PR index: it must remain clean without RPP leaks
        $this->flushSession();
        $siswaSession = ['user_id' => $siswa->id_siswa, 'user_type' => 'siswa', 'user_name' => $siswa->nm_siswa];
        $siswaResponse = $this->withSession($siswaSession)
            ->get(route('siswa.notifikasi_pr.index'));

        $siswaResponse->assertStatus(200);
        $siswaResponse->assertDontSee('Modul Pembelajaran Tata Surya');
        $siswaResponse->assertDontSee('Permintaan RPP');
    }
}
