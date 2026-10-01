<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $userType = session('user_type');

        $mapelList = MataPelajaran::withCount(['bab', 'siswas'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_mapel', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })->orderBy('nama_mapel')->paginate(10)->withQueryString();

        $total = MataPelajaran::count();

        if ($userType === 'admin') {
            return view('admin.mapel.index', compact('mapelList', 'total', 'search'));
        }

        return view('mapel.index', compact('mapelList', 'total', 'search'));
    }

    public function store(Request $request)
    {
        if (session('user_type') === 'admin') {
            return redirect()->route('mapel.index')->with('error', 'Administrator hanya memiliki izin melihat dan menghapus mata pelajaran. Pengelolaan konten dilakukan oleh Guru.');
        }

        $request->validate([
            'nama_mapel' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\&\-\(\)\/\.]+$/u',
                'unique:mata_pelajaran,nama_mapel',
            ],
            'deskripsi'  => 'nullable|string|max:1000',
        ], [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'nama_mapel.min'      => 'Nama mata pelajaran minimal 2 karakter.',
            'nama_mapel.max'      => 'Nama mata pelajaran maksimal 100 karakter.',
            'nama_mapel.unique'   => 'Nama mata pelajaran sudah terdaftar.',
            'nama_mapel.regex'    => 'Nama mata pelajaran hanya boleh mengandung huruf, angka, spasi, dan karakter (&, -, (, ), /, .).',
            'deskripsi.max'       => 'Deskripsi mata pelajaran maksimal 1000 karakter.',
        ]);

        MataPelajaran::create([
            'nama_mapel' => trim($request->nama_mapel),
            'deskripsi'  => $request->deskripsi,
        ]);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        if (session('user_type') === 'admin') {
            return redirect()->route('mapel.index')->with('error', 'Administrator hanya memiliki izin melihat dan menghapus mata pelajaran. Pengelolaan konten dilakukan oleh Guru.');
        }

        $mapel = MataPelajaran::findOrFail($id);

        $request->validate([
            'nama_mapel' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\&\-\(\)\/\.]+$/u',
                'unique:mata_pelajaran,nama_mapel,' . $id . ',id_mapel',
            ],
            'deskripsi'  => 'nullable|string|max:1000',
        ], [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'nama_mapel.min'      => 'Nama mata pelajaran minimal 2 karakter.',
            'nama_mapel.max'      => 'Nama mata pelajaran maksimal 100 karakter.',
            'nama_mapel.unique'   => 'Nama mata pelajaran sudah terdaftar.',
            'nama_mapel.regex'    => 'Nama mata pelajaran hanya boleh mengandung huruf, angka, spasi, dan karakter (&, -, (, ), /, .).',
            'deskripsi.max'       => 'Deskripsi mata pelajaran maksimal 1000 karakter.',
        ]);

        $mapel->update([
            'nama_mapel' => trim($request->nama_mapel),
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
