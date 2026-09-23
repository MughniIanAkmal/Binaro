<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index(Request $request)
    {
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
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|regex:/^[0-9]+$/|unique:guru,nip',
            'email' => 'nullable|email|unique:guru,email',
            'no_hp' => 'nullable|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => 'nullable|string|unique:guru,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $validated['nama_guru'] = $validated['nama'];

        Guru::create($validated);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => ['nullable', 'regex:/^[0-9]+$/', Rule::unique('guru', 'nip')->ignore($guru->id_guru, 'id_guru')],
            'email' => ['nullable', 'email', Rule::unique('guru', 'email')->ignore($guru->id_guru, 'id_guru')],
            'no_hp' => 'nullable|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => ['nullable', 'string', Rule::unique('guru', 'username')->ignore($guru->id_guru, 'id_guru')],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $validated['nama_guru'] = $validated['nama'];

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $guru->update($validated);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus.');
    }
}