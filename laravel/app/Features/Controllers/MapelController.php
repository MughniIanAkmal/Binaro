<?php

namespace App\Features\Controllers;

use App\Features\Models\Guru;
use App\Features\Models\Kelas;
use App\Features\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $userType = session('user_type');

        $mapelList = MataPelajaran::with(['kelas', 'gurus'])->withCount('bab')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_mapel', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            // Guru hanya melihat mapel yang dia ampu (pivot guru_mapel).
            ->when($userType === 'guru', function ($q) {
                $q->whereHas('gurus', fn ($sub) => $sub->where('guru.id_guru', session('user_id')));
            })
            ->orderBy('nama_mapel')->paginate(10)->withQueryString();

        $total = $userType === 'guru'
            ? $mapelList->total()
            : MataPelajaran::count();

        $kelasList = Kelas::orderBy('tingkat')->orderBy('pararel')->get();
        $guruList = Guru::orderBy('nama_guru')->get(['id_guru', 'nama_guru']);

        if ($userType === 'admin') {
            return view('admin.mapel.index', compact('mapelList', 'total', 'search', 'kelasList', 'guruList'));
        }

        return view('mapel.index', compact('mapelList', 'total', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\&\-\(\)\/\.]+$/u',
                Rule::unique('mata_pelajaran', 'nama_mapel')->where(
                    fn ($q) => $q->where('id_rooms', $request->id_rooms)
                ),
            ],
            'deskripsi' => 'nullable|string|max:1000',
            'id_rooms' => ['nullable', 'integer', 'exists:kelas,id_rooms'],
            'id_guru' => ['nullable', 'integer', 'exists:guru,id_guru'],
        ], [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'nama_mapel.min' => 'Nama mata pelajaran minimal 2 karakter.',
            'nama_mapel.max' => 'Nama mata pelajaran maksimal 100 karakter.',
            'nama_mapel.unique' => 'Mata pelajaran dengan nama dan kelas yang sama sudah terdaftar.',
            'nama_mapel.regex' => 'Nama mata pelajaran hanya boleh mengandung huruf, angka, spasi, dan karakter (&, -, (, ), /, .).',
            'deskripsi.max' => 'Deskripsi mata pelajaran maksimal 1000 karakter.',
            'id_rooms.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'id_guru.exists' => 'Guru yang dipilih tidak ditemukan.',
        ]);

        $mapel = MataPelajaran::create([
            'nama_mapel' => trim($request->nama_mapel),
            'deskripsi' => $request->deskripsi,
            'id_rooms' => $request->id_rooms,
        ]);

        // Guru pengampu disimpan di pivot guru_mapel (1 mapel = 1 guru).
        $mapel->gurus()->sync($request->filled('id_guru') ? [$request->integer('id_guru')] : []);

        $redirectUrl = $request->header('referer');
        if ($redirectUrl && (str_contains($redirectUrl, 'mapel-belajar') || str_contains($redirectUrl, 'guru/mapel'))) {
            return redirect($redirectUrl)->with('success', 'Mata pelajaran berhasil ditambahkan.');
        }

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $mapel = MataPelajaran::findOrFail($id);

        $request->validate([
            'nama_mapel' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\&\-\(\)\/\.]+$/u',
                Rule::unique('mata_pelajaran', 'nama_mapel')
                    ->ignore($id, 'id_mapel')
                    ->where(
                        fn ($q) => $q->where('id_rooms', $request->id_rooms)
                    ),
            ],
            'deskripsi' => 'nullable|string|max:1000',
            'id_rooms' => ['nullable', 'integer', 'exists:kelas,id_rooms'],
            'id_guru' => ['nullable', 'integer', 'exists:guru,id_guru'],
        ], [
            'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
            'nama_mapel.min' => 'Nama mata pelajaran minimal 2 karakter.',
            'nama_mapel.max' => 'Nama mata pelajaran maksimal 100 karakter.',
            'nama_mapel.unique' => 'Mata pelajaran dengan nama dan kelas yang sama sudah terdaftar.',
            'nama_mapel.regex' => 'Nama mata pelajaran hanya boleh mengandung huruf, angka, spasi, dan karakter (&, -, (, ), /, .).',
            'deskripsi.max' => 'Deskripsi mata pelajaran maksimal 1000 karakter.',
            'id_rooms.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'id_guru.exists' => 'Guru yang dipilih tidak ditemukan.',
        ]);

        $mapel->update([
            'nama_mapel' => trim($request->nama_mapel),
            'deskripsi' => $request->deskripsi,
            'id_rooms' => $request->id_rooms,
        ]);

        $mapel->gurus()->sync($request->filled('id_guru') ? [$request->integer('id_guru')] : []);

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
