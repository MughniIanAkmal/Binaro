<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $mapelList = MataPelajaran::when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('nama_mapel', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        })->orderBy('nama_mapel')->paginate(10)->withQueryString();

        $total = MataPelajaran::count();

        return view('mapel.index', compact('mapelList', 'total', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:100|unique:mata_pelajaran,nama_mapel',
            'deskripsi'  => 'nullable|string|max:1000',
        ], [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'nama_mapel.unique'   => 'Nama mata pelajaran sudah terdaftar.',
            'nama_mapel.max'      => 'Nama mata pelajaran maksimal 100 karakter.',
        ]);

        MataPelajaran::create([
            'nama_mapel' => $request->nama_mapel,
            'deskripsi'  => $request->deskripsi,
        ]);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $mapel = MataPelajaran::findOrFail($id);

        $request->validate([
            'nama_mapel' => 'required|string|max:100|unique:mata_pelajaran,nama_mapel,' . $id . ',id_mapel',
            'deskripsi'  => 'nullable|string|max:1000',
        ], [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'nama_mapel.unique'   => 'Nama mata pelajaran sudah terdaftar.',
            'nama_mapel.max'      => 'Nama mata pelajaran maksimal 100 karakter.',
        ]);

        $mapel->update([
            'nama_mapel' => $request->nama_mapel,
            'deskripsi'  => $request->deskripsi,
        ]);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $mapel = MataPelajaran::findOrFail($id);

        try {
            $mapel->delete();
            return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('mapel.index')->with('error', 'Gagal menghapus. Mata pelajaran masih digunakan oleh data lain.');
        }
    }
}
