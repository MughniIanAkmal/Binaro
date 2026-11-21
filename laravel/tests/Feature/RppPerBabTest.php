<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Bab;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use App\Models\RppCatatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan pengelolaan RPP per bab sesuai hasil analisis regulasi:
 * - RPP disusun per bab, bukan per mapel.
 * - Fase diturunkan dari tingkat kelas, bukan diketik manual.
 * - Komponen wajib (tujuan, langkah, asesmen) menghalangi pengajuan.
 * - Asesmen minimal dua teknik berbeda.
 * - Catatan supervisi tersimpan sebagai riwayat.
 */
class RppPerBabTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;

    protected $admin;

    protected $mapel;

    protected $mapelLain;

    protected $kelas;

    protected $bab;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nip' => 'ADMIN001',
            'nama_admin' => 'Administrator',
            'password' => 'password123',
        ]);

        $this->mapel = MataPelajaran::create(['nama_mapel' => 'Matematika']);
        $this->mapelLain = MataPelajaran::create(['nama_mapel' => 'Bahasa Indonesia']);

        $this->kelas = Kelas::create(['pararel' => 'Kelas 4B', 'tingkat' => 4]);

        $this->bab = Bab::create(['id_mapel' => $this->mapel->id_mapel, 'nama_bab' => 'Bab 1: Pecahan']);

        $this->guru = Guru::create([
            'nama_guru' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'email' => 'budi@sekolah.sch.id',
            'username' => 'guru_budi',
            'password' => 'password123',
            'id_rooms' => $this->kelas->id_rooms,
        ]);
    }

    private function sessionGuru(): array
    {
        return ['user_id' => $this->guru->id_guru, 'user_type' => 'guru', 'user_name' => $this->guru->nama_guru];
    }

    private function sessionAdmin(): array
    {
        return ['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nama_admin];
    }

    private function payloadDasar(array $override = []): array
    {
        return array_merge([
            'id_mapel' => $this->mapel->id_mapel,
            'id_bab' => $this->bab->id_bab,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Pecahan Senilai',
            'action' => 'publish',
            'tujuan' => '1',
            'langkah' => '1',
            'asesmen' => '1',
            'asesmen_teknik' => ['Observasi', 'Tes tulis'],
        ], $override);
    }

    public function test_fase_diturunkan_dari_tingkat_kelas(): void
    {
        $this->assertSame('Fase B', $this->kelas->fase());

        $kelasA = Kelas::create(['pararel' => 'Kelas 2A', 'tingkat' => 2]);
        $kelasC = Kelas::create(['pararel' => 'Kelas 5A', 'tingkat' => 5]);

        $this->assertSame('Fase A', $kelasA->fase());
        $this->assertSame('Fase C', $kelasC->fase());
    }

    public function test_guru_bisa_membuat_rpp_untuk_bab_yang_sama_dengan_mapel_sama(): void
    {
        $response = $this->withSession($this->sessionGuru())
            ->post(route('guru.rpp.store'), $this->payloadDasar());

        $response->assertRedirect(route('guru.rpp.index'));

        $rpp = Rpp::where('judul_rpp', 'Pecahan Senilai')->first();
        $this->assertNotNull($rpp);
        $this->assertSame($this->bab->id_bab, (int) $rpp->id_bab);
        $this->assertSame('Fase B', $rpp->fase);
    }

    public function test_bab_dari_mapel_lain_ditolak(): void
    {
        $babLain = Bab::create(['id_mapel' => $this->mapelLain->id_mapel, 'nama_bab' => 'Bab 1: Teks']);

        $response = $this->withSession($this->sessionGuru())
            ->post(route('guru.rpp.store'), $this->payloadDasar(['id_bab' => $babLain->id_bab]));

        $response->assertStatus(422);
        $this->assertDatabaseMissing('rpp', ['id_bab' => $babLain->id_bab]);
    }

    public function test_pengajuan_ditolak_bila_komponen_wajib_belum_lengkap(): void
    {
        $response = $this->withSession($this->sessionGuru())
            ->post(route('guru.rpp.store'), $this->payloadDasar([
                'tujuan' => null,
                'langkah' => null,
            ]));

        $response->assertSessionHasErrors('komponen');
        $this->assertDatabaseMissing('rpp', ['judul_rpp' => 'Pecahan Senilai']);
    }

    public function test_pengajuan_ditolak_bila_asesmen_kurang_dari_dua_teknik(): void
    {
        $response = $this->withSession($this->sessionGuru())
            ->post(route('guru.rpp.store'), $this->payloadDasar([
                'asesmen_teknik' => ['Tes tulis'],
            ]));

        $response->assertSessionHasErrors('asesmen_teknik');
        $this->assertDatabaseMissing('rpp', ['judul_rpp' => 'Pecahan Senilai']);
    }

    public function test_draf_tetap_bisa_disimpan_walau_belum_lengkap(): void
    {
        $response = $this->withSession($this->sessionGuru())
            ->post(route('guru.rpp.store'), $this->payloadDasar([
                'action' => 'draft',
                'id_bab' => null,
                'tujuan' => null,
                'langkah' => null,
                'asesmen' => null,
                'asesmen_teknik' => null,
            ]));

        $response->assertRedirect(route('guru.rpp.index'));

        $rpp = Rpp::where('judul_rpp', 'Pecahan Senilai')->first();
        $this->assertNotNull($rpp);
        $this->assertSame('draft', $rpp->status);
        $this->assertFalse($rpp->komponenWajibLengkap());
    }

    public function test_skor_kelengkapan_membedakan_wajib_dan_disarankan(): void
    {
        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_bab' => $this->bab->id_bab,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Uji Skor',
            'status' => 'draft',
            'komponen_checklist' => [
                'tujuan' => true, 'langkah' => true, 'asesmen' => true,
                'media' => true, 'lkpd' => true,
            ],
        ]);

        $this->assertTrue($rpp->komponenWajibLengkap());
        $this->assertSame(2, $rpp->jumlahDisarankanTerisi());
        $this->assertSame(79, $rpp->skorKelengkapan()); // 70 (wajib) + 8.57 (2/7 disarankan)
    }

    public function test_catatan_supervisi_disimpan_sebagai_riwayat(): void
    {
        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_bab' => $this->bab->id_bab,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Pecahan Senilai',
            'status' => 'menunggu_review',
        ]);

        $this->withSession($this->sessionAdmin())
            ->patch(route('rpp.updateStatus', $rpp->id_rpp), [
                'status' => 'perlu_revisi',
                'catatan_revisi' => 'Lengkapi rubrik asesmen produk.',
            ])->assertRedirect();

        $this->withSession($this->sessionAdmin())
            ->patch(route('rpp.updateStatus', $rpp->id_rpp), [
                'status' => 'perlu_revisi',
                'catatan_revisi' => 'Tambahkan refleksi peserta didik.',
            ])->assertRedirect();

        $catatan = RppCatatan::where('id_rpp', $rpp->id_rpp)->orderBy('id_catatan')->get();

        $this->assertCount(2, $catatan);
        $this->assertSame('Lengkapi rubrik asesmen produk.', $catatan[0]->isi);
        $this->assertSame('Tambahkan refleksi peserta didik.', $catatan[1]->isi);
    }

    public function test_guru_tidak_bisa_mengunduh_rpp_guru_lain(): void
    {
        $guruLain = Guru::create([
            'nama_guru' => 'Siti Aminah',
            'nip' => '198501012010011002',
            'email' => 'siti@sekolah.sch.id',
            'username' => 'guru_siti',
            'password' => 'password123',
        ]);

        $rpp = Rpp::create([
            'id_guru' => $guruLain->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Milik Guru Lain',
            'status' => 'draft',
        ]);

        $this->withSession($this->sessionGuru())
            ->get(route('guru.rpp.download', $rpp->id_rpp))
            ->assertForbidden();

        // Admin tetap boleh membuka dokumen untuk verifikasi.
        $this->withSession($this->sessionAdmin())
            ->get(route('rpp.download', $rpp->id_rpp))
            ->assertOk();
    }
}
