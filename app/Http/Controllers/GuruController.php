<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index()
    {
        $guru = Guru::latest()->paginate(10);
        return view('guru.index', compact('guru'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:guru,nip',
            'email' => 'required|email|unique:guru,email',
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => 'required|string|unique:guru,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);

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
            'nip' => ['nullable', 'string', Rule::unique('guru', 'nip')->ignore($guru->id_guru, 'id_guru')],
            'email' => ['required', 'email', Rule::unique('guru', 'email')->ignore($guru->id_guru, 'id_guru')],
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => ['required', 'string', Rule::unique('guru', 'username')->ignore($guru->id_guru, 'id_guru')],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
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