<?php

namespace Tests\Feature;

use App\Models\AbsensiSetting;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

    public function test_admin_can_view_absensi_settings_page_with_3_zones()
    {
        $response = $this->withSession(['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nama_admin])
            ->get(route('admin.absensi.settings'));

        $response->assertOk();
        $response->assertSee('Pengaturan Jam Absensi', false);
        $response->assertSee('Datang Lebih Awal');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Terlambat');
        $response->assertSee('Sekolah Tutup');
        $response->assertSee('Simpan Pengaturan Jam');
    }

    public function test_admin_can_update_absensi_settings_successfully()
    {
        $response = $this->withSession(['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nama_admin])
            ->post(route('admin.absensi.settings.update'), [
                'batas_awal'  => '07:00',
                'batas_tepat' => '08:00',
                'batas_tutup' => '14:30',
            ]);

        $response->assertRedirect(route('admin.absensi.settings'));
        $response->assertSessionHas('success');

        $this->assertEquals('07:00', AbsensiSetting::get('batas_awal'));
        $this->assertEquals('08:00', AbsensiSetting::get('batas_tepat'));
        $this->assertEquals('14:30', AbsensiSetting::get('batas_tutup'));
    }

    public function test_admin_cannot_input_invalid_chronological_settings()
    {
        // batas_awal >= batas_tepat
        $response = $this->withSession(['user_id' => $this->admin->id_admin, 'user_type' => 'admin', 'user_name' => $this->admin->nama_admin])
            ->post(route('admin.absensi.settings.update'), [
                'batas_awal'  => '08:30',
                'batas_tepat' => '08:00',
                'batas_tutup' => '15:00',
            ]);

        $response->assertSessionHas('error');
        $this->assertNotEquals('08:30', AbsensiSetting::get('batas_awal'));
    }
}
