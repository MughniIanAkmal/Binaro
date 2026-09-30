<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function dashboard()
    {
        $guru = null;
        if (session()->has('user_id') && session('user_type') === 'guru') {
            $guru = Guru::find(session('user_id'));
        }
        $mapels = MataPelajaran::withCount(['bab'])->get();
        return view('guru.mapel.index', compact('mapels', 'guru'));
    }

    public function index(Request $request)
    {
        if (session('user_type') === 'guru') {
            return redirect()->route('guru.dashboard');
        }
        $search = $request->get('search') ?? $request->get('q');
        $guru = Guru::when($search, function ($query, $search) {
            $query->where('nama_guru', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })->latest('id_guru')->paginate(10)->withQueryString();

        return view('guru.index', compact('guru', 'search'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nip' => 'nullable|string|max:30|unique:guru,nip',
            'email' => 'required|email|max:100|unique:guru,email',
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => 'required|string|max:50|unique:guru,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = [
            'nama_guru' => $validated['nama'],
            'nip' => $validated['nip'] ?? null,
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ];

        Guru::create($data);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($guru->id_guru, 'id_guru')],
            'email' => ['required', 'email', 'max:100', Rule::unique('guru', 'email')->ignore($guru->id_guru, 'id_guru')],
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => ['required', 'string', 'max:50', Rule::unique('guru', 'username')->ignore($guru->id_guru, 'id_guru')],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'nama_guru' => $validated['nama'],
            'nip' => $validated['nip'] ?? null,
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'username' => $validated['username'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $guru->update($data);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus.');
    }
}