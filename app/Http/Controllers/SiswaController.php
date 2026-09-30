<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function dashboard()
    {
        $siswa = null;
        if (session()->has('user_id') && session('user_type') === 'siswa') {
            $siswa = Siswa::with(['kelas', 'mataPelajaran'])->find(session('user_id'));
        }
        return view('siswa.dashboard', compact('siswa'));
    }

    public function index(Request $request)
    {
        if (session('user_type') === 'siswa') {
            return redirect()->route('siswa.dashboard');
        }

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
            'nis' => 'required|string|max:20|unique:siswa,nisn',
            'email' => 'required|email|max:100|unique:siswa,email',
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => 'required|string|max:50|unique:siswa,username',
            'password' => 'required|string|min:6|confirmed',
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ]);

        $data = [
            'nm_siswa' => $validated['nama'],
            'nisn' => $validated['nis'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'username' => $validated['username'],
            'password' => $validated['password'], // store plain-text password for backward compatibility
            'id_mapel' => $validated['id_mapel'] ?? null,
            'id_rooms' => $validated['id_rooms'] ?? null,
        ];

        $newSiswa = Siswa::create($data);

        // Auto generate QR code
        Barcode::firstOrCreate(
            ['id_siswa' => $newSiswa->id_siswa],
            ['kode_barcode' => Barcode::buatKodeUnik()]
        );

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
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
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswa', 'nisn')->ignore($siswa->id_siswa, 'id_siswa')],
            'email' => ['required', 'email', 'max:100', Rule::unique('siswa', 'email')->ignore($siswa->id_siswa, 'id_siswa')],
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => ['required', 'string', 'max:50', Rule::unique('siswa', 'username')->ignore($siswa->id_siswa, 'id_siswa')],
            'password' => 'nullable|string|min:6|confirmed',
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ]);

        $data = [
            'nm_siswa' => $validated['nama'],
            'nisn' => $validated['nis'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'username' => $validated['username'],
            'id_mapel' => $validated['id_mapel'] ?? null,
            'id_rooms' => $validated['id_rooms'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $siswa->update($data);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}