<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use App\Models\Siswa;
use Database\Seeders\SiswaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingAuditTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $siswa;
    protected $kelas;
    protected $mapel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kelas = Kelas::create(['pararel' => '4A']);
        $this->mapel = MataPelajaran::create(['nama_mapel' => 'Matematika']);

        $this->guru = Guru::create([
            'nama_guru' => 'Guru Penguji',
            'nip' => '198501012010011099',
            'email' => 'guru.test@school.id',
            'username' => 'gurutest',
            'password' => bcrypt('password123'),
        ]);

        $this->siswa = Siswa::create([
            'nm_siswa' => 'Siswa Penguji',
            'nisn' => '9988776655',
            'email' => 'siswa.test@school.id',
            'id_rooms' => $this->kelas->id_rooms,
            'id_mapel' => $this->mapel->id_mapel,
            'username' => 'siswatest',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/guru/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/siswa/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/absensi/rekap');
        $response->assertRedirect('/login');
    }

    public function test_commit_login_form_authenticates_students_and_guru_dashboards()
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Binaro')
            ->assertSee(route('login.post'), false);

        $this->post('/login', [
            'role' => 'siswa',
            'username' => '',
            'identity' => $this->siswa->nisn,
            'password' => 'password123',
        ])->assertRedirect('/siswa/dashboard');

        $this->withSession([])->post('/login', [
            'role' => 'guru',
            'username' => '',
            'identity' => $this->siswa->nisn,
            'password' => 'password123',
        ])->assertRedirect('/login')
            ->assertSessionHas('error');

        $this->withSession([])->post('/login', [
            'role' => 'siswa',
            'username' => '',
            'identity' => $this->guru->nip,
            'password' => 'password123',
        ])->assertRedirect('/login')
            ->assertSessionHas('error');

        $this->withSession([])->get('/siswa/dashboard')
            ->assertOk()
            ->assertSee('Halo, Siswa Penguji!');

        $this->post('/login', [
            'role' => 'guru',
            'username' => '',
            'identity' => $this->guru->nip,
            'password' => 'password123',
        ])->assertRedirect('/guru/dashboard');

        $this->withSession([
            'user_id' => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ])->get('/guru/dashboard')
            ->assertOk()
            ->assertSee('Guru Penguji');
    }

    public function test_student_seeder_runs_repeatedly_with_current_schema()
    {
        $this->seed(SiswaSeeder::class);
        $this->seed(SiswaSeeder::class);

        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345601',
            'nm_siswa' => 'Aditya Pratama',
        ]);
        $this->assertSame(5, Siswa::whereIn('nisn', [
            '0012345601',
            '0012345602',
            '0012345603',
            '0012345604',
            '0012345605',
        ])->count());
    }

    public function test_siswa_role_route_protections()
    {
        // Login as siswa
        $session = [
            'user_id' => $this->siswa->id_siswa,
            'user_type' => 'siswa',
            'user_name' => $this->siswa->nm_siswa,
        ];

        // Access Siswa Dashboard
        $response = $this->withSession($session)->get('/siswa/dashboard');
        $response->assertStatus(200);

        // Access Siswa Mapel
        $response = $this->withSession($session)->get('/siswa/mapel');
        $response->assertStatus(200);

        // Unauthorized access to Guru routes -> redirected to siswa dashboard
        $response = $this->withSession($session)->get('/guru/dashboard');
        $response->assertRedirect('/siswa/dashboard');

        // Unauthorized access to Admin routes -> redirected to siswa dashboard
        $response = $this->withSession($session)->get('/admin/absensi/rekap');
        $response->assertRedirect('/siswa/dashboard');

        // Unauthorized access to Guru RPP -> redirected to siswa dashboard
        $response = $this->withSession($session)->get('/rpp');
        $response->assertRedirect('/siswa/dashboard');

        // Visiting /siswa as siswa redirects to /siswa/dashboard
        $response = $this->withSession($session)->get('/siswa');
        $response->assertRedirect('/siswa/dashboard');
    }

    public function test_guru_role_route_protections()
    {
        // Login as guru
        $session = [
            'user_id' => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ];

        // Access Guru Dashboard
        $response = $this->withSession($session)->get('/guru/dashboard');
        $response->assertStatus(200);

        // Access Guru Materi Index
        $response = $this->withSession($session)->get('/guru/materi');
        $response->assertStatus(200);

        // Access Mapel
        $response = $this->withSession($session)->get('/mapel');
        $response->assertStatus(200);

        // Access RPP
        $response = $this->withSession($session)->get('/rpp');
        $response->assertStatus(200);

        // Access Absensi
        $response = $this->withSession($session)->get('/absensi');
        $response->assertStatus(200);

        // Access Siswa Management (Allowed for Guru & Admin)
        $response = $this->withSession($session)->get('/siswa');
        $response->assertStatus(200);

        // Unauthorized access to Admin QR Management
        $response = $this->withSession($session)->get('/admin/qr-siswa');
        $response->assertRedirect('/guru/dashboard');

        // Unauthorized access to Guru Account Creation (Admin only)
        $response = $this->withSession($session)->get('/guru/create');
        $response->assertRedirect('/guru/dashboard');

        // Access Guru Jadwal Mengajar
        $response = $this->withSession($session)->get('/guru/jadwal-mengajar');
        $response->assertStatus(200);

        // Access Guru Rekap Absensi
        $response = $this->withSession($session)->get('/guru/absensi/rekap');
        $response->assertStatus(200);
    }

    public function test_admin_role_route_access()
    {
        // Login as admin
        $session = [
            'user_id' => 1,
            'user_type' => 'admin',
            'user_name' => 'Administrator',
        ];

        // Access Admin Dashboard & root admin
        $response = $this->withSession($session)->get('/admin/dashboard');
        $response->assertStatus(200);

        $response = $this->withSession($session)->get('/admin');
        $response->assertRedirect('/admin/dashboard');

        // Access Admin Rekap
        $response = $this->withSession($session)->get('/admin/absensi/rekap');
        $response->assertStatus(200);

        // Access Admin QR Siswa
        $response = $this->withSession($session)->get('/admin/qr-siswa');
        $response->assertStatus(200);

        // Access Guru CRUD
        $response = $this->withSession($session)->get('/guru');
        $response->assertStatus(200);

        $response = $this->withSession($session)->get('/guru/create');
        $response->assertStatus(200);

        // Access Siswa CRUD
        $response = $this->withSession($session)->get('/siswa');
        $response->assertStatus(200);

        $response = $this->withSession($session)->get('/siswa/create');
        $response->assertStatus(200);

        // Access RPP
        $response = $this->withSession($session)->get('/rpp');
        $response->assertStatus(200);

        // Access Absensi
        $response = $this->withSession($session)->get('/absensi');
        $response->assertStatus(200);
    }

    public function test_rpp_show_view_resolves_cleanly()
    {
        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'id_rooms' => $this->kelas->id_rooms,
            'judul_rpp' => 'Modul Geometri Dasar',
            'deskripsi' => 'Pengenalan bangun datar',
            'status' => 'draft',
        ]);

        $session = [
            'user_id' => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ];

        $response = $this->withSession($session)->get(route('rpp.show', $rpp->id_rpp));
        $response->assertStatus(200);
        $response->assertSee('Modul Geometri Dasar');
    }
}
