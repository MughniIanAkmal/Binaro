<?php

namespace Tests\Feature;

use App\Models\AbsensiSetting;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengaturan jam absen sekarang berupa pop-up di halaman QR & Absensi
 * (dan Presensi Siswa), bukan halaman terpisah. URL lama tetap dialihkan.
 */
class AbsensiSettingsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nip' => '198001012005011001',
            'username' => 'admin_test',
            'password' => 'admin123',
            'nama_admin' => 'Admin Utama',
        ]);
    }

    private function sessionAdmin(): array
    {
        return ['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nama_admin];
    }

    public function test_halaman_lama_dialihkan_ke_modal_di_qr_absensi()
    {
        $response = $this->withSession($this->sessionAdmin())
            ->get(route('admin.absensi.settings'));

        $response->assertRedirect(route('admin.qr_absensi.index', ['open_settings' => 1]));
    }

    public function test_modal_pengaturan_tampil_di_halaman_qr_absensi()
    {
        $response = $this->withSession($this->sessionAdmin())
            ->get(route('admin.qr_absensi.index'));

        $response->assertOk();
        $response->assertSee('id="settingsModal"', false);
        $response->assertSee('Pengaturan Jam Absen');
        $response->assertSee('Lebih Awal');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Terlambat');
        $response->assertSee('Sekolah Tutup');
        $response->assertSee('Simpan Pengaturan');
        // Tombol pembuka ada di bar filter tabel, bukan di header halaman.
        $response->assertSee('onclick="openSettingsModal()"', false);
    }

    public function test_modal_dibuka_otomatis_saat_open_settings_ada_di_url()
    {
        $response = $this->withSession($this->sessionAdmin())
            ->get(route('admin.qr_absensi.index', ['open_settings' => 1]));

        $response->assertOk();
        $response->assertSee('openSettingsModal();', false);
    }

    public function test_sidebar_tidak_lagi_menautkan_halaman_pengaturan_jam()
    {
        $response = $this->withSession($this->sessionAdmin())
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee(route('admin.absensi.settings'), false);
    }

    public function test_admin_can_update_absensi_settings_successfully()
    {
        $response = $this->withSession($this->sessionAdmin())
            ->from(route('admin.qr_absensi.index'))
            ->post(route('admin.absensi.settings.update'), [
                'batas_awal' => '07:00',
                'batas_tepat' => '08:00',
                'batas_tutup' => '14:30',
            ]);

        // Kembali ke halaman asal (tempat modal dibuka), bukan halaman terpisah.
        $response->assertRedirect(route('admin.qr_absensi.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('07:00', AbsensiSetting::get('batas_awal'));
        $this->assertEquals('08:00', AbsensiSetting::get('batas_tepat'));
        $this->assertEquals('14:30', AbsensiSetting::get('batas_tutup'));
    }

    public function test_admin_cannot_input_invalid_chronological_settings()
    {
        // batas_awal >= batas_tepat
        $response = $this->withSession($this->sessionAdmin())
            ->post(route('admin.absensi.settings.update'), [
                'batas_awal' => '08:30',
                'batas_tepat' => '08:00',
                'batas_tutup' => '15:00',
            ]);

        $response->assertSessionHas('error');
        $this->assertNotEquals('08:30', AbsensiSetting::get('batas_awal'));
    }
}
