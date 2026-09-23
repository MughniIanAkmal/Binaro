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
            ->leftJoin(
                'mata_pelajaran',
                'jadwal_mata_pelajaran.id_mapel',
                '=',
                'mata_pelajaran.id_mapel'
            )
            ->leftJoin(
                'guru',
                'jadwal_mata_pelajaran.id_guru',
                '=',
                'guru.id_guru'
            )
            ->leftJoin(
                '{kelas}',
                'jadwal_mata_pelajaran.id_rooms',
                '=',
                '{id_rooms}.id_rooms'
            )
            ->select(
                'jadwal_mata_pelajaran.*',
                'mata_pelajaran.nama_mapel',
                'guru.nama_guru',
                DB::raw("COALESCE(kelas.pararel, 'Belum Set') as nama_rooms")
            )
            ->get();

        return view('jadwal.index', compact('jadwals'));
    }

    // Menampilkan form tambah jadwal
    public function create()
    {
        $nama = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();

        return view('jadwal.create', compact(
            'nama',
            'guru',
            'mapel'
        ));
    }

    // Menyimpan jadwal baru
    public function store(Request $request)
    {
        $request->validate([
            'hari'     => 'required',
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => 'required',
            'id_guru'  => 'required',
            'id_rooms' => 'required|exists:rooms,id_rooms',
        ], [
            'jam.regex' => 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).',
            'id_rooms.required' => 'Kelas wajib dipilih!',
        ]);

        Jadwal::create([
            'hari'     => $request->hari,
            'jam'      => $request->jam,
            'id_mapel' => $request->id_mapel,
            'id_guru'  => $request->id_guru,
            'id_rooms' => $request->id_rooms,
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal sudah ditambahkan.');
    }
    // Menampilkan form edit
    public function edit($id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $nama = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();

        return view('jadwal.edit', compact(
            'jadwal',
            'nama',
            'guru',
            'mapel'
        ));
    }

    // Mengupdate jadwal
    public function update(Request $request, $id)
    {
        $request->validate([
            'hari'     => 'required',
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => 'required',
            'id_guru'  => 'required',
            'id_rooms' => 'required',
        ]);

        $jadwal = Jadwal::findOrFail($id);

        $jadwal->update([
            'hari'     => $request->hari,
            'jam'      => $request->jam,
            'id_mapel' => $request->id_mapel,
            'id_guru'  => $request->id_guru,
            'id_rooms' => $request->id_rooms,
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal sudah diubah.');
    }
}