<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_school_and_roles()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Binaro');
        $response->assertSee('SD Negeri Kalitapen 1');
        $response->assertSee('images/sekolah.jpg');
        $response->assertSee('Siswa');
        $response->assertSee('Guru');
        $response->assertSee('Admin');
    }

    public function test_login_siswa_with_role_and_identity()
    {
        $siswa = Siswa::create([
            'nm_siswa' => 'Budi Santoso',
            'nisn' => '1234567890',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login/process', [
            'role' => 'siswa',
            'username' => 'Budi Santoso',
            'identity' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/siswa/dashboard');
        $this->assertEquals($siswa->id_siswa, session('user_id'));
        $this->assertEquals('siswa', session('user_type'));
        $this->assertEquals('siswa', session('role'));
        $this->assertTrue(session('login'));

        // Test beranda redirect
        $beranda = $this->get('/beranda');
        $beranda->assertRedirect('/siswa/dashboard');
    }

    public function test_login_guru_with_role_and_identity()
    {
        $guru = Guru::create([
            'nama_guru' => 'Bu Guru Ani',
            'nip' => '198501012010012001',
            'password' => Hash::make('guru123'),
        ]);

        $response = $this->post('/login/process', [
            'role' => 'guru',
            'username' => 'Bu Guru Ani',
            'identity' => '198501012010012001',
            'password' => 'guru123',
        ]);

        $response->assertRedirect('/guru/dashboard');
        $this->assertEquals($guru->id_guru, session('user_id'));
        $this->assertEquals('guru', session('user_type'));
        $this->assertEquals('guru', session('role'));
        $this->assertTrue(session('login'));

        // Test beranda redirect
        $beranda = $this->get('/beranda');
        $beranda->assertRedirect('/guru/dashboard');
    }

    public function test_login_admin()
    {
        $admin = Admin::create([
            'nama_admin' => 'Admin Utama',
            'nip' => 'admin_kalitapen',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->post('/login/process', [
            'role' => 'admin',
            'username' => 'admin_kalitapen',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertEquals($admin->id_admin, session('user_id'));
        $this->assertEquals('admin', session('user_type'));
        $this->assertEquals('admin', session('role'));
        $this->assertTrue(session('login'));

        // Test beranda redirect
        $beranda = $this->get('/beranda');
        $beranda->assertRedirect('/admin/dashboard');
    }
}
