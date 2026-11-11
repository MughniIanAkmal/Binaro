<?php
namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_tidak_membocorkan_password(): void
    {
        $siswa = Siswa::create(['nisn' => '111222333', 'nm_siswa' => 'Siswa Uji', 'password' => 'rahasia123']);
        $this->assertArrayNotHasKey('password', $siswa->toArray());
    }

    public function test_guru_tidak_membocorkan_password(): void
    {
        $guru = Guru::create(['nip' => '19900101', 'nama_guru' => 'Guru Uji', 'password' => 'rahasia123']);
        $this->assertArrayNotHasKey('password', $guru->toArray());
    }

    public function test_admin_tidak_membocorkan_password(): void
    {
        $admin = Admin::create(['nip' => 'ADM001', 'nama_admin' => 'Admin Uji', 'password' => 'rahasia123']);
        $this->assertArrayNotHasKey('password', $admin->toArray());
    }

    public function test_tiga_model_punya_trait_token_api(): void
    {
        foreach ([new Siswa, new Guru, new Admin] as $model) {
            $this->assertTrue(in_array(\Laravel\Sanctum\HasApiTokens::class, class_uses_recursive($model)));
        }
    }

    public function test_guard_sanctum_bisa_diresolusi(): void
    {
        $this->assertNotNull(auth()->guard('sanctum'));
    }
}
