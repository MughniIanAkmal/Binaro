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

        return view('jadwal.create', compact('kelas', 'guru', 'mapel'));
    }

    // Menyimpan jadwal baru
    public function store(Request $request)
    {
        $request->validate([
            'hari'     => 'required',
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => 'required',
            'id_guru'  => 'required',
            'id_kelas' => 'required|exists:kelas,id_rooms',
        ], [
            'jam.regex' => 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).',
            'id_kelas.required' => 'Kelas wajib dipilih!',
        ]);

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
}