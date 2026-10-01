<?php

namespace Tests\Feature;

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiRekapTest extends TestCase
{
    use RefreshDatabase;

    public function test_rekap_lists_only_students_with_absence_on_selected_date_and_future_date_is_empty(): void
    {
        $guru = Guru::create([
            'nama_guru' => 'Guru Rekap',
            'nip' => '198501012010011050',
            'email' => 'guru.rekap@example.test',
            'password' => 'password',
        ]);
        $kelas = Kelas::create(['pararel' => 'Kelas Rekap']);
        $siswaTercatat = Siswa::create([
            'nm_siswa' => 'Siswa Sudah Dicatat',
            'nisn' => '9000000001',
            'id_rooms' => $kelas->id_rooms,
            'password' => 'password',
        ]);
        $siswaBelumTercatat = Siswa::create([
            'nm_siswa' => 'Siswa Belum Dicatat',
            'nisn' => '9000000002',
            'id_rooms' => $kelas->id_rooms,
            'password' => 'password',
        ]);

        $today = today()->toDateString();
        Absen::create([
            'id_guru' => $guru->id_guru,
            'id_siswa' => $siswaTercatat->id_siswa,
            'tanggal' => $today,
            'status' => 'Hadir',
            'metode' => 'manual_guru',
        ]);
        $this->assertTrue(Absen::where('id_siswa', $siswaTercatat->id_siswa)
            ->whereDate('tanggal', $today)
            ->exists());

        $session = [
            'user_type' => 'guru',
            'user_id' => $guru->id_guru,
            'user_name' => $guru->nama_guru,
        ];

        $todayResponse = $this->withSession($session)->get('/guru/absensi/rekap?tanggal='.$today);
        $todayResponse->assertOk();
        $todayResponse->assertViewHas('selectedDate', $today);
        $todayList = $todayResponse->viewData('siswaList');
        $this->assertSame(1, $todayList->total());
        $this->assertTrue($todayList->getCollection()->contains('id_siswa', $siswaTercatat->id_siswa));
        $this->assertFalse($todayList->getCollection()->contains('id_siswa', $siswaBelumTercatat->id_siswa));

        $futureDate = today()->addDay()->toDateString();
        $futureResponse = $this->withSession($session)->get('/guru/absensi/rekap?tanggal='.$futureDate);
        $futureResponse->assertOk();
        $futureResponse->assertViewHas('selectedDate', $futureDate);
        $futureList = $futureResponse->viewData('siswaList');
        $this->assertSame(0, $futureList->total());
    }
}
