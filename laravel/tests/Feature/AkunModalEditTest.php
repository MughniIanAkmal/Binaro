<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkunModalEditTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $guru;

    protected $siswa;

    protected $kelas;

    protected $mapel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nip' => 'ADMIN001',
            'nama_admin' => 'Administrator',
            'password' => 'password123',
        ]);

        $this->kelas = Kelas::create(['pararel' => 'Kelas 4A']);

        $this->mapel = MataPelajaran::create(['nama_mapel' => 'Matematika']);

        $this->guru = Guru::create([
            'nama_guru' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'email' => 'budi@sekolah.sch.id',
            'username' => 'guru_budi',
            'password' => 'password123',
        ]);

        $this->siswa = Siswa::create([
            'nm_siswa' => 'Ahmad Rizki',
            'nisn' => '0012345678',
            'email' => 'ahmad@sekolah.sch.id',
            'id_rooms' => $this->kelas->id_rooms,
            'username' => 'ahmad123',
            'password' => 'password123',
        ]);
    }

    protected function sessionAdmin(): array
    {
        return ['user_id' => $this->admin->id_admin, 'user_type' => 'admin'];
    }

    public function test_halaman_daftar_guru_menampilkan_modal_edit_per_baris(): void
    {
        $response = $this->withSession($this->sessionAdmin())->get(route('guru.data.index'));

        $response->assertOk()
            ->assertSee('data-open-edit-guru="'.$this->guru->id_guru.'"', false)
            ->assertSee('id="modal-edit-guru-'.$this->guru->id_guru.'"', false)
            ->assertSee('Budi Santoso');
    }

    public function test_halaman_daftar_siswa_menampilkan_modal_edit_per_baris(): void
    {
        $response = $this->withSession($this->sessionAdmin())->get(route('siswa.data.index'));

        $response->assertOk()
            ->assertSee('data-open-edit-siswa="'.$this->siswa->id_siswa.'"', false)
            ->assertSee('id="modal-edit-siswa-'.$this->siswa->id_siswa.'"', false)
            ->assertSee('Ahmad Rizki');
    }

    public function test_update_guru_gagal_validasi_kembali_ke_halaman_daftar_dengan_penanda_modal_edit(): void
    {
        $response = $this->withSession($this->sessionAdmin())
            ->from(route('guru.data.index'))
            ->put(route('guru.data.update', $this->guru->id_guru), [
                'edit_id' => $this->guru->id_guru,
                'nama' => '',
                'email' => 'bukan-email',
                'username' => 'guru_budi',
            ]);

        $response->assertRedirect(route('guru.data.index'))
            ->assertSessionHasErrors(['nama', 'email'])
            ->assertSessionHasInput('edit_id', (string) $this->guru->id_guru);

        $halaman = $this->withSession($this->sessionAdmin())->get(route('guru.data.index'));

        $halaman->assertOk()
            ->assertSee('bukaModal(\'modal-edit-guru-'.$this->guru->id_guru.'\')', false)
            ->assertSee('name="edit_id" value="'.$this->guru->id_guru.'"', false)
            // Error validasi tampil di dalam modal, bukan alert global di atas halaman.
            ->assertDontSee('Terjadi Kesalahan Input');
    }

    public function test_update_siswa_gagal_validasi_kembali_ke_halaman_daftar_dengan_penanda_modal_edit(): void
    {
        $response = $this->withSession($this->sessionAdmin())
            ->from(route('siswa.data.index'))
            ->put(route('siswa.data.update', $this->siswa->id_siswa), [
                'edit_id' => $this->siswa->id_siswa,
                'nama' => '',
                'nis' => '0012345678',
                'email' => 'bukan-email',
                'username' => 'ahmad123',
            ]);

        $response->assertRedirect(route('siswa.data.index'))
            ->assertSessionHasErrors(['nama', 'email'])
            ->assertSessionHasInput('edit_id', (string) $this->siswa->id_siswa);

        $halaman = $this->withSession($this->sessionAdmin())->get(route('siswa.data.index'));

        $halaman->assertOk()
            ->assertSee('bukaModal(\'modal-edit-siswa-'.$this->siswa->id_siswa.'\')', false)
            ->assertSee('name="edit_id" value="'.$this->siswa->id_siswa.'"', false)
            // Error validasi tampil di dalam modal, bukan alert global di atas halaman.
            ->assertDontSee('Terjadi kesalahan input');
    }

    public function test_update_guru_berhasil_tetap_redirect_dengan_pesan_sukses(): void
    {
        $response = $this->withSession($this->sessionAdmin())
            ->put(route('guru.data.update', $this->guru->id_guru), [
                'nama' => 'Budi Santoso Baru',
                'nip' => '198501012010011',
                'email' => 'budi.baru@sekolah.sch.id',
                'username' => 'guru_budi',
            ]);

        $response->assertRedirect(route('guru.data.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('guru', [
            'id_guru' => $this->guru->id_guru,
            'nama_guru' => 'Budi Santoso Baru',
        ]);
    }
}
