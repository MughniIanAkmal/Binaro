<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Penjagaan otorisasi RPP:
 * 1. Hanya admin yang boleh mengubah status supervisi.
 * 2. Guru hanya boleh mengubah/menghapus RPP miliknya.
 * 3. Guru tidak bisa membuat RPP atas nama guru lain.
 */
class RppAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;

    protected $guruLain;

    protected $admin;

    protected $mapel;

    protected $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nip' => 'ADMIN001',
            'nama_admin' => 'Administrator',
            'password' => 'password123',
        ]);

        $this->mapel = MataPelajaran::create(['nama_mapel' => 'Matematika']);

        $this->kelas = Kelas::create(['pararel' => 'Kelas 4A']);

        $this->guru = Guru::create([
            'nama_guru' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'email' => 'budi@sekolah.sch.id',
            'username' => 'guru_budi',
            'password' => 'password123',
        ]);

        $this->guruLain = Guru::create([
            'nama_guru' => 'Siti Aminah',
            'nip' => '198501012010011002',
            'email' => 'siti@sekolah.sch.id',
            'username' => 'guru_siti',
            'password' => 'password123',
        ]);
    }

    private function sessionGuru(Guru $guru): array
    {
        return ['user_id' => $guru->id_guru, 'user_type' => 'guru', 'user_name' => $guru->nama_guru];
    }

    private function sessionAdmin(): array
    {
        return ['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nama_admin];
    }

    private function buatRpp(Guru $guru, array $override = []): Rpp
    {
        return Rpp::create(array_merge([
            'id_guru' => $guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Pecahan Senilai',
            'status' => 'menunggu_review',
        ], $override));
    }

    public function test_guru_tidak_bisa_menyetujui_rpp_sendiri(): void
    {
        $rpp = $this->buatRpp($this->guru);

        // Middleware EnsureRole menolak dengan redirect; yang penting status
        // RPP tidak berubah dan guru tidak mendapat akses ke endpoint supervisi.
        $response = $this->withSession($this->sessionGuru($this->guru))
            ->patch(route('rpp.updateStatus', $rpp->id_rpp), ['status' => 'terverifikasi']);

        $response->assertRedirect();
        $this->assertSame('menunggu_review', $rpp->fresh()->status);
    }

    public function test_admin_bisa_mengubah_status_supervisi(): void
    {
        $rpp = $this->buatRpp($this->guru);

        $response = $this->withSession($this->sessionAdmin())
            ->patch(route('rpp.updateStatus', $rpp->id_rpp), [
                'status' => 'terverifikasi',
                'catatan_revisi' => null,
            ]);

        $response->assertRedirect();
        $this->assertSame('terverifikasi', $rpp->fresh()->status);
    }

    public function test_guru_tidak_bisa_mengubah_rpp_milik_guru_lain(): void
    {
        $rpp = $this->buatRpp($this->guruLain);

        $response = $this->withSession($this->sessionGuru($this->guru))
            ->put(route('guru.rpp.update', $rpp->id_rpp), [
                'id_mapel' => $this->mapel->id_mapel,
                'id_rooms' => $this->kelas->id_rooms,
                'judul_rpp' => 'Diubah Guru Lain',
            ]);

        $response->assertForbidden();
        $this->assertSame('Pecahan Senilai', $rpp->fresh()->judul_rpp);
    }

    public function test_guru_tidak_bisa_menghapus_rpp_milik_guru_lain(): void
    {
        $rpp = $this->buatRpp($this->guruLain);

        $response = $this->withSession($this->sessionGuru($this->guru))
            ->delete(route('guru.rpp.destroy', $rpp->id_rpp));

        $response->assertForbidden();
        $this->assertDatabaseHas('rpp', ['id_rpp' => $rpp->id_rpp]);
    }

    public function test_guru_bisa_mengubah_dan_menghapus_rpp_miliknya_sendiri(): void
    {
        $rpp = $this->buatRpp($this->guru, ['status' => 'draft']);

        $update = $this->withSession($this->sessionGuru($this->guru))
            ->put(route('guru.rpp.update', $rpp->id_rpp), [
                'id_mapel' => $this->mapel->id_mapel,
                'id_rooms' => $this->kelas->id_rooms,
                'judul_rpp' => 'Pecahan Senilai Revisi',
                'action' => 'draft',
            ]);

        $update->assertRedirect();
        $this->assertSame('Pecahan Senilai Revisi', $rpp->fresh()->judul_rpp);

        $hapus = $this->withSession($this->sessionGuru($this->guru))
            ->delete(route('guru.rpp.destroy', $rpp->id_rpp));

        $hapus->assertRedirect();
        $this->assertDatabaseMissing('rpp', ['id_rpp' => $rpp->id_rpp]);
    }

    public function test_guru_tidak_bisa_membuat_rpp_atas_nama_guru_lain(): void
    {
        $response = $this->withSession($this->sessionGuru($this->guru))
            ->post(route('guru.rpp.store'), [
                // id_guru dipalsukan ke guru lain; server harus mengabaikannya.
                'id_guru' => $this->guruLain->id_guru,
                'id_mapel' => $this->mapel->id_mapel,
                'id_rooms' => $this->kelas->id_rooms,
                'judul_rpp' => 'RPP Palsu',
                'action' => 'draft',
            ]);

        $response->assertRedirect();

        $rpp = Rpp::where('judul_rpp', 'RPP Palsu')->first();
        $this->assertNotNull($rpp);
        $this->assertSame($this->guru->id_guru, (int) $rpp->id_guru);
    }
}
