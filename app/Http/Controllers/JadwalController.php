<?php

namespace App\Http\Controllers;

use App\Models\JadwalMataPelajaran as Jadwal;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    // Menampilkan semua jadwal
    public function index()
    {
        if (session('user_type') === 'guru') {
            return redirect()->route('guru.jadwal.index');
        }

        $jadwals = Jadwal::query()
            ->with(['mataPelajaran', 'guru', 'kelas'])
            ->get();

        return view('jadwal.index', compact('jadwals'));
    }

    // Menampilkan form tambah jadwal
    public function create()
    {
        $kelas = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();
        $jamTutup = \App\Models\AbsensiSetting::get('batas_tutup', '12:00');
        $jamAwal = \App\Models\AbsensiSetting::get('batas_awal', '07:00');

        return view('jadwal.create', compact('kelas', 'guru', 'mapel', 'jamTutup', 'jamAwal'));
    }

    // Menampilkan detail jadwal
    public function show($id)
    {
        $jadwal = Jadwal::with(['mataPelajaran', 'guru', 'kelas'])->findOrFail($id);

        return view('jadwal.show', compact('jadwal'));
    }

    // Validasi format jam dan mencegah jam mundur / sama / melebihi jam tutup sekolah
    private function validateJamPelajaran($jam)
    {
        $jamClean = preg_replace('/\s+/', '', str_replace(':', '.', (string)$jam));
        $parts = explode('-', $jamClean);
        if (count($parts) !== 2) {
            return 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).';
        }

        $jamMulai = trim($parts[0]);
        $jamSelesai = trim($parts[1]);

        $mulaiParts = explode('.', $jamMulai);
        $selesaiParts = explode('.', $jamSelesai);

        if (count($mulaiParts) !== 2 || count($selesaiParts) !== 2) {
            return 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).';
        }

        $hMulai = (int)$mulaiParts[0];
        $mMulai = (int)$mulaiParts[1];
        $hSelesai = (int)$selesaiParts[0];
        $mSelesai = (int)$selesaiParts[1];

        if ($hMulai < 0 || $hMulai > 23 || $mMulai < 0 || $mMulai > 59 || $hSelesai < 0 || $hSelesai > 23 || $mSelesai < 0 || $mSelesai > 59) {
            return 'Nilai jam atau menit tidak valid! Jam harus rentang 00-23 dan menit rentang 00-59.';
        }

        $totalMulai = ($hMulai * 60) + $mMulai;
        $totalSelesai = ($hSelesai * 60) + $mSelesai;

        if ($totalSelesai <= $totalMulai) {
            return "Jam pelajaran terbalik atau tidak valid! Jam selesai ({$jamSelesai}) tidak boleh lebih awal atau sama dengan jam mulai ({$jamMulai}).";
        }

        // Cek jam tutup sekolah
        $batasTutup = \App\Models\AbsensiSetting::get('batas_tutup', '12:00');
        $tutupClean = str_replace('.', ':', (string)$batasTutup);
        $tutupParts = explode(':', $tutupClean);
        $hTutup = (int)($tutupParts[0] ?? 12);
        $mTutup = (int)($tutupParts[1] ?? 0);
        $totalTutup = ($hTutup * 60) + $mTutup;

        if ($totalSelesai > $totalTutup) {
            $jamTutupDisplay = str_replace(':', '.', $batasTutup);
            return "Jam selesai pelajaran ({$jamSelesai}) melebihi jam tutup sekolah ({$jamTutupDisplay})! Sesuaikan jadwal atau ubah jam tutup sekolah di menu Pengaturan Jam Absen.";
        }

        return null;
    }

    // Menyimpan jadwal baru
    public function store(Request $request)
    {
        if ($request->has('jam')) {
            $request->merge(['jam' => preg_replace('/\s+/', '', str_replace(':', '.', (string)$request->jam))]);
        }
        $request->validate([
            'hari'     => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => ['required', 'exists:mata_pelajaran,id_mapel'],
            'id_guru'  => ['required', 'exists:guru,id_guru'],
            'id_kelas' => ['required', 'exists:kelas,id_rooms'],
        ], [
            'hari.in'           => 'Pilihan hari tidak valid!',
            'id_mapel.exists'   => 'Mata pelajaran yang dipilih tidak valid!',
            'id_guru.exists'    => 'Guru yang dipilih tidak valid!',
            'jam.regex'         => 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).',
            'id_kelas.required' => 'Kelas wajib dipilih!',
            'id_kelas.exists'   => 'Kelas yang dipilih tidak valid!',
        ]);

        // Cek validasi jam mundur / human error
        $jamError = $this->validateJamPelajaran($request->jam);
        if ($jamError) {
            return back()->withInput()->withErrors(['jam' => $jamError])->with('error', $jamError);
        }

        // Cek duplikat: hari + jam + id_kelas + id_guru
        $exists = Jadwal::where('hari', $request->hari)
            ->where('jam', $request->jam)
            ->where('id_rooms', $request->id_kelas)
            ->where('id_guru', $request->id_guru)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Jadwal sudah ada (duplikat hari, jam, guru, dan kelas).');
        }

        Jadwal::create([
            'hari'     => $request->hari,
            'jam'      => $request->jam,
            'id_mapel' => $request->id_mapel,
            'id_guru'  => $request->id_guru,
            'id_rooms' => $request->id_kelas,
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $kelas = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();
        $jamTutup = \App\Models\AbsensiSetting::get('batas_tutup', '12:00');
        $jamAwal = \App\Models\AbsensiSetting::get('batas_awal', '07:00');

        return view('jadwal.edit', compact('jadwal', 'kelas', 'guru', 'mapel', 'jamTutup', 'jamAwal'));
    }

    // Mengupdate jadwal
    public function update(Request $request, $id)
    {
        if ($request->has('jam')) {
            $request->merge(['jam' => preg_replace('/\s+/', '', str_replace(':', '.', (string)$request->jam))]);
        }
        $request->validate([
            'hari'     => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => ['required', 'exists:mata_pelajaran,id_mapel'],
            'id_guru'  => ['required', 'exists:guru,id_guru'],
            'id_kelas' => ['required', 'exists:kelas,id_rooms'],
        ], [
            'hari.in'           => 'Pilihan hari tidak valid!',
            'id_mapel.exists'   => 'Mata pelajaran yang dipilih tidak valid!',
            'id_guru.exists'    => 'Guru yang dipilih tidak valid!',
            'jam.regex'         => 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).',
            'id_kelas.required' => 'Kelas wajib dipilih!',
            'id_kelas.exists'   => 'Kelas yang dipilih tidak valid!',
        ]);

        // Cek validasi jam mundur / human error
        $jamError = $this->validateJamPelajaran($request->jam);
        if ($jamError) {
            return back()->withInput()->withErrors(['jam' => $jamError])->with('error', $jamError);
        }

        $jadwal = Jadwal::findOrFail($id);

        // Cek duplikat tapi exclude record ini
        $exists = Jadwal::where('hari', $request->hari)
            ->where('jam', $request->jam)
            ->where('id_rooms', $request->id_kelas)
            ->where('id_guru', $request->id_guru)
            ->where('id_jadwal', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Jadwal sudah ada (duplikat hari, jam, guru, dan kelas).');
        }

        $jadwal->update([
            'hari'     => $request->hari,
            'jam'      => $request->jam,
            'id_mapel' => $request->id_mapel,
            'id_guru'  => $request->id_guru,
            'id_rooms' => $request->id_kelas,
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil diubah.');
    }

    // Menghapus jadwal
    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}