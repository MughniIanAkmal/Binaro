<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalMataPelajaran as Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $kelas;
    protected $mapel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapel = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Matematika Test'],
            ['deskripsi' => 'Deskripsi Mapel Matematika']
        );

        $this->guru = Guru::firstOrCreate(
            ['nip' => '198501012010011009'],
            ['nama_guru' => 'Guru Penguji', 'email' => 'guru.test@school.id', 'password' => 'password']
        );

        $this->kelas = Kelas::firstOrCreate(
            ['pararel' => 'Kelas 1-A Test'],
            ['tingkat' => 1]
        );
    }

    public function test_admin_can_view_jadwal_index()
    {
        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->get(route('jadwal.index'));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Mata Pelajaran');
    }

    public function test_guru_schedule_only_shows_the_authenticated_gurus_jadwal()
    {
        $guruLain = Guru::create([
            'nip' => '198501012010011010',
            'nama_guru' => 'Guru Lain',
            'email' => 'guru.lain@school.id',
            'password' => 'password',
        ]);

        Jadwal::create([
            'hari' => 'Senin',
            'jam' => '07.30-08.00',
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'id_rooms' => $this->kelas->id_rooms,
        ]);
        Jadwal::create([
            'hari' => 'Senin',
            'jam' => '09.00-10.00',
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $guruLain->id_guru,
            'id_rooms' => $this->kelas->id_rooms,
        ]);

        $response = $this->withSession(['user_type' => 'guru', 'user_id' => $this->guru->id_guru])
            ->get(route('guru.jadwal.index'));

        $response->assertOk();
        $response->assertSee('07.30-08.00');
        $response->assertDontSee('09.00-10.00');
    }

    public function test_guru_schedule_clears_invalid_teacher_session()
    {
        Jadwal::create([
            'hari' => 'Senin',
            'jam' => '07.30-08.00',
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'id_rooms' => $this->kelas->id_rooms,
        ]);

        $response = $this->withSession(['user_type' => 'guru', 'user_id' => 999999])
            ->get(route('guru.jadwal.index'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $response->assertSessionMissing('user_id');
    }

    public function test_admin_can_view_jadwal_detail()
    {
        $jadwal = Jadwal::create([
            'hari' => 'Senin',
            'jam' => '07.30-09.00',
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'id_rooms' => $this->kelas->id_rooms,
        ]);

        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->get(route('jadwal.show', $jadwal->id_jadwal));

        $response->assertStatus(200);
        $response->assertSee('Detail Jadwal Mata Pelajaran');
        $response->assertSee('Matematika Test');
        $response->assertSee('07.30-09.00');

        $jadwal->delete();
    }

    public function test_admin_can_create_valid_jadwal()
    {
        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->post(route('jadwal.store'), [
                'hari' => 'Selasa',
                'jam' => '08.00-09.30',
                'id_mapel' => $this->mapel->id_mapel,
                'id_guru' => $this->guru->id_guru,
                'id_kelas' => $this->kelas->id_rooms,
            ]);

        $response->assertRedirect(route('jadwal.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_mata_pelajaran', [
            'hari' => 'Selasa',
            'jam' => '08.00-09.30',
            'id_mapel' => $this->mapel->id_mapel,
        ]);

        Jadwal::where('jam', '08.00-09.30')->delete();
    }

    public function test_admin_cannot_input_backwards_jam()
    {
        // Contoh jam mundur: 09.00-07.00
        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->from(route('jadwal.create'))
            ->post(route('jadwal.store'), [
                'hari' => 'Rabu',
                'jam' => '09.00-07.00',
                'id_mapel' => $this->mapel->id_mapel,
                'id_guru' => $this->guru->id_guru,
                'id_kelas' => $this->kelas->id_rooms,
            ]);

        $response->assertRedirect(route('jadwal.create'));
        $response->assertSessionHasErrors('jam');
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('jadwal_mata_pelajaran', [
            'jam' => '09.00-07.00',
        ]);
    }

    public function test_admin_cannot_input_same_start_and_end_jam()
    {
        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->from(route('jadwal.create'))
            ->post(route('jadwal.store'), [
                'hari' => 'Kamis',
                'jam' => '08.00-08.00',
                'id_mapel' => $this->mapel->id_mapel,
                'id_guru' => $this->guru->id_guru,
                'id_kelas' => $this->kelas->id_rooms,
            ]);

        $response->assertRedirect(route('jadwal.create'));
        $response->assertSessionHasErrors('jam');
    }

    public function test_admin_can_update_jadwal()
    {
        $jadwal = Jadwal::create([
            'hari' => 'Jumat',
            'jam' => '07.00-08.00',
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'id_rooms' => $this->kelas->id_rooms,
        ]);

        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->put(route('jadwal.update', $jadwal->id_jadwal), [
                'hari' => 'Jumat',
                'jam' => '07.00-08.30',
                'id_mapel' => $this->mapel->id_mapel,
                'id_guru' => $this->guru->id_guru,
                'id_kelas' => $this->kelas->id_rooms,
            ]);

        $response->assertRedirect(route('jadwal.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_mata_pelajaran', [
            'id_jadwal' => $jadwal->id_jadwal,
            'jam' => '07.00-08.30',
        ]);

        $jadwal->delete();
    }

    public function test_admin_cannot_update_with_backwards_jam()
    {
        $jadwal = Jadwal::create([
            'hari' => 'Sabtu',
            'jam' => '07.00-08.00',
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'id_rooms' => $this->kelas->id_rooms,
        ]);

        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->from(route('jadwal.edit', $jadwal->id_jadwal))
            ->put(route('jadwal.update', $jadwal->id_jadwal), [
                'hari' => 'Sabtu',
                'jam' => '10.00-08.00',
                'id_mapel' => $this->mapel->id_mapel,
                'id_guru' => $this->guru->id_guru,
                'id_kelas' => $this->kelas->id_rooms,
            ]);

        $response->assertRedirect(route('jadwal.edit', $jadwal->id_jadwal));
        $response->assertSessionHasErrors('jam');

        $jadwal->delete();
    }

    public function test_admin_can_delete_jadwal()
    {
        $jadwal = Jadwal::create([
            'hari' => 'Senin',
            'jam' => '10.00-11.30',
            'id_mapel' => $this->mapel->id_mapel,
            'id_guru' => $this->guru->id_guru,
            'id_rooms' => $this->kelas->id_rooms,
        ]);

        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->delete(route('jadwal.destroy', $jadwal->id_jadwal));

        $response->assertRedirect(route('jadwal.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('jadwal_mata_pelajaran', [
            'id_jadwal' => $jadwal->id_jadwal,
        ]);
    }

    public function test_admin_cannot_input_jam_exceeding_school_closing_time()
    {
        // School closing time is default 12:00 in absensi_settings
        \App\Models\AbsensiSetting::set('batas_tutup', '12:00');

        $response = $this->withSession(['user_type' => 'admin', 'user_id' => 1])
            ->from(route('jadwal.create'))
            ->post(route('jadwal.store'), [
                'hari'     => 'Senin',
                'jam'      => '10.00-13.00', // Exceeds 12.00
                'id_mapel' => $this->mapel->id_mapel,
                'id_guru'  => $this->guru->id_guru,
                'id_kelas' => $this->kelas->id_rooms,
            ]);

        $response->assertRedirect(route('jadwal.create'));
        $response->assertSessionHasErrors('jam');
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('jadwal_mata_pelajaran', [
            'jam' => '10.00-13.00',
        ]);
    }
}
