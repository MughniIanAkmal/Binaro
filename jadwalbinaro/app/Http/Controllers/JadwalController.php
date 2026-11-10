<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    // Menampilkan semua jadwal
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
                'kelas',
                'jadwal_mata_pelajaran.id_rooms',
                '=',
                'kelas.id_rooms'
            )
            ->select(
                'jadwal_mata_pelajaran.*',
                'mata_pelajaran.nama_mapel',
                'guru.nama_guru',
                // Jika kelas.pararel NULL, gunakan teks 'Belum Set'
                DB::raw("COALESCE(kelas.pararel, 'Belum Set') as nama_kelas")
            )
            ->get();

        return view('jadwal.index', compact('jadwals'));
    }

    // Menampilkan form tambah jadwal
    public function create()
    {
        $kelas = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();

        return view('jadwal.create', compact(
            'kelas',
            'guru',
            'mapel'
        ));
    }

    // Menyimpan jadwal baru
    // Menyimpan jadwal baru
    public function store(Request $request)
    {
        $request->validate([
            'hari'     => 'required',
            'jam'      => ['required', 'regex:/^[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}$/'],
            'id_mapel' => 'required',
            'id_guru'  => 'required',
            'id_kelas' => 'required|exists:kelas,id_rooms', // Pastikan ID kelas valid ada di database
        ], [
            'jam.regex' => 'Format jam tidak valid! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).',
            'id_kelas.required' => 'Kelas wajib dipilih!',
        ]);

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

        return view('jadwal.edit', compact(
            'jadwal',
            'kelas',
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
            'id_kelas' => 'required',
        ]);

        $jadwal = Jadwal::findOrFail($id);

        $jadwal->update([
            'hari'     => $request->hari,
            'jam'      => $request->jam,
            'id_mapel' => $request->id_mapel,
            'id_guru'  => $request->id_guru,
            'id_rooms' => $request->id_kelas, // Pastikan disimpan ke id_rooms
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil diubah.');
    }
}
