<?php

namespace Tests\Feature;

use App\Models\CapaianPembelajaran;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use App\Models\RppSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RppWizardTest extends TestCase
{
    use RefreshDatabase;

    private Guru $guru;

    private MataPelajaran $mapel;

    private Kelas $kelas;

    private array $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Guru::create([
            'nip' => '199001012015032001',
            'nama_guru' => 'Sari Aminah',
            'email' => 'sari@example.test',
            'password' => 'password',
        ]);
        $this->mapel = MataPelajaran::create(['nama_mapel' => 'Matematika']);
        $this->kelas = Kelas::create(['pararel' => 'Kelas 4A', 'tingkat' => 4]);
        $this->session = [
            'user_id' => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ];
    }

    public function test_guru_can_create_draft_in_the_rpp_wizard_and_resume_a_saved_session(): void
    {
        $response = $this->withSession($this->session)->post(route('guru.rpp.wizard.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Bangun Datar',
            'semester' => 'ganjil',
            'tahun_ajaran' => '2026/2027',
            'total_pertemuan' => 1,
            'alokasi_jp' => 2,
            'action' => 'draft',
        ]);

        $rpp = Rpp::where('judul_rpp', 'Bangun Datar')->firstOrFail();
        $response->assertRedirect(route('guru.rpp.wizard.edit', [$rpp->id_rpp, 'step' => 1, 'pertemuan' => 1]));
        $this->assertSame('Fase B', $rpp->fase);
        $this->assertSame(70, $rpp->alokasi_waktu_menit);
        $this->assertSame('2 JP', $rpp->alokasi_waktu);
        $this->assertSame('draft', $rpp->status);
        $this->assertDatabaseHas('rpp_sessions', [
            'id_rpp' => $rpp->id_rpp,
            'nomor_pertemuan' => 1,
        ]);

        $resume = $this->withSession($this->session)
            ->get(route('guru.rpp.wizard.edit', [$rpp->id_rpp, 'step' => 1, 'pertemuan' => 1]));

        $resume->assertOk()->assertSee('Bangun Datar')->assertSee('Informasi Umum');
    }

    public function test_guru_can_autosave_a_session_without_submitting_for_review(): void
    {
        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Pecahan',
            'status' => 'draft',
        ]);
        $session = RppSession::create(['id_rpp' => $rpp->id_rpp, 'nomor_pertemuan' => 1]);

        $response = $this->withSession($this->session)
            ->postJson(route('guru.rpp.wizard.autosave', $rpp->id_rpp), [
                'pertemuan' => 1,
                'konten' => [
                    'tp_list' => [
                        ['teks' => 'Murid mengidentifikasi pecahan', 'dimensi' => ['Bernalar Kritis']],
                    ],
                    'pertanyaan_pemantik' => ['Bagaimana membandingkan dua pecahan?'],
                ],
            ]);

        $response->assertOk()->assertJsonPath('status', 'draft');
        $this->assertSame('draft', $rpp->fresh()->status);
        $this->assertSame('Murid mengidentifikasi pecahan', $session->fresh()->konten['tp_list'][0]['teks']);
    }

    public function test_guru_can_only_select_active_cp_matching_subject_and_phase(): void
    {
        $cp = CapaianPembelajaran::create([
            'id_mapel' => $this->mapel->id_mapel,
            'fase' => 'B',
            'elemen' => 'Geometri',
            'cp_text' => 'Murid memahami bentuk geometri sederhana.',
            'is_active' => true,
        ]);

        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Geometri',
            'fase' => 'Fase B',
            'status' => 'draft',
        ]);
        RppSession::create(['id_rpp' => $rpp->id_rpp, 'nomor_pertemuan' => 1]);

        $response = $this->withSession($this->session)
            ->postJson(route('guru.rpp.wizard.autosave', $rpp->id_rpp), [
                'pertemuan' => 1,
                'konten' => [
                    'cp_id' => $cp->id_cp,
                    'cp_text' => $cp->cp_text,
                ],
            ]);

        $response->assertOk();
        $this->assertSame($cp->id_cp, RppSession::where('id_rpp', $rpp->id_rpp)->first()->konten['cp_id']);
    }
}
