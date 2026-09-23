<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? $request->get('q');
        $idRooms = $request->get('id_rooms');

        $siswa = Siswa::with(['mataPelajaran', 'kelas', 'barcode'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nm_siswa', 'like', "%{$search}%")
                      ->orWhere('nisn', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($idRooms, fn($q) => $q->where('id_rooms', $idRooms))
            ->latest('id_siswa')
            ->paginate(10)
            ->withQueryString();

        $kelasList = Kelas::all();

        return view('siswa.index', compact('siswa', 'search', 'kelasList', 'idRooms'));
    }

    public function create()
    {
        $mapel = MataPelajaran::all();
        $kelas = Kelas::all();
        return view('siswa.create', compact('mapel', 'kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nis' => 'nullable|regex:/^[0-9]+$/|unique:siswa,nisn',
            'email' => 'nullable|email|unique:siswa,email',
            'no_hp' => 'nullable|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => 'nullable|string|unique:siswa,username',
            'password' => 'required|string|min:6|confirmed',
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ]);

        $validated['nm_siswa'] = $validated['nama'];
        $validated['nisn'] = $validated['nis'] ?? null;
        // Password stored as plain text per request

        $newSiswa = Siswa::create($validated);

        // Generate QR code secara otomatis untuk siswa baru
        \App\Models\Barcode::firstOrCreate(
            ['id_siswa' => $newSiswa->id_siswa],
            ['kode_barcode' => \App\Models\Barcode::buatKodeUnik()]
        );

        return redirect()->route('siswa.index')->with('success', 'Data siswa dan QR code berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $mapel = MataPelajaran::all();
        $kelas = Kelas::all();
        return view('siswa.edit', compact('siswa', 'mapel', 'kelas'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nis' => ['nullable', 'regex:/^[0-9]+$/', Rule::unique('siswa', 'nisn')->ignore($siswa->id_siswa, 'id_siswa')],
            'email' => ['nullable', 'email', Rule::unique('siswa', 'email')->ignore($siswa->id_siswa, 'id_siswa')],
            'no_hp' => 'nullable|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => ['nullable', 'string', Rule::unique('siswa', 'username')->ignore($siswa->id_siswa, 'id_siswa')],
            'password' => 'nullable|string|min:6|confirmed',
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ]);

        $validated['nm_siswa'] = $validated['nama'];
        $validated['nisn'] = $validated['nis'] ?? null;

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $siswa->update($validated);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}