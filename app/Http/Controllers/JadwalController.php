<?php

namespace App\Http\Controllers;

use App\Models\JadwalMataPelajaran;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    // Menampilkan semua jadwal
    public function index()
    {
        // Menggunakan Eloquent Relationship bawaan dari JadwalMataPelajaran
        $jadwals = JadwalMataPelajaran::query()->with(['mataPelajaran', 'guru', 'kelas'])->get();
        return view('jadwal/index', compact('jadwals'));
    }

    // Menampilkan form tambah jadwal
    public function create()
    {
        $kelas = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();

        return view('jadwal/create', compact('kelas', 'guru', 'mapel'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'hari'     => 'required',
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => 'required',
            'id_guru'  => 'required',
            'id_kelas' => 'required|exists:kelas,id_rooms',
        ], [
            'jam.regex'         => 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).',
            'id_kelas.required' => 'Kelas wajib dipilih!',
        ]);

        JadwalMataPelajaran::query()->create([
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
        $jadwal = JadwalMataPelajaran::findOrFail($id);
        $kelas  = Kelas::all();
        $guru   = Guru::all();
        $mapel  = MataPelajaran::all();

        return view('jadwal.edit', compact('jadwal', 'kelas', 'guru', 'mapel'));
    }

    // Mengupdate jadwal
    public function update(Request $request, $id)
    {
        $request->validate([
            'hari'     => 'required',
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => 'required',
            'id_guru'  => 'required',
            'id_kelas' => 'required',
        ]);

        $jadwal = JadwalMataPelajaran::findOrFail($id);

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
}
