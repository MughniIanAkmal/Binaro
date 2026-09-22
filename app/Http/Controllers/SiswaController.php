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
    public function index()
    {
        $siswa = Siswa::with(['mataPelajaran', 'kelas'])->latest()->paginate(10);
        return view('siswa.index', compact('siswa'));
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
            'nis' => ['nullable', 'string', Rule::unique('siswa', 'nis')->ignore($siswa->id_siswa, 'id_siswa')],
            'email' => ['required', 'email', Rule::unique('siswa', 'email')->ignore($siswa->id_siswa, 'id_siswa')],
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'username' => ['required', 'string', Rule::unique('siswa', 'username')->ignore($siswa->id_siswa, 'id_siswa')],
            'password' => 'nullable|string|min:6|confirmed',
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
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