<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalMataPelajaran;
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
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s.,\'\-]+$/u'],
            'nip' => ['nullable', 'string', 'max:30', 'regex:/^[0-9\- ]*$/', 'unique:guru,nip'],
            'email' => 'required|email|max:100|unique:guru,email',
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]*$/'],
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:500',
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', 'unique:guru,username'],
            'password' => 'required|string|min:6|max:100|confirmed',
        ], [
            'nama.required'    => 'Nama guru wajib diisi.',
            'nama.max'         => 'Nama guru maksimal 100 karakter.',
            'nama.regex'       => 'Nama guru hanya boleh berisi huruf, spasi, dan gelar (. , \' -). Simbol lain dilarang.',
            'nip.regex'        => 'NIP hanya boleh berisi angka dan tanda hubung.',
            'no_hp.regex'      => 'Nomor HP hanya boleh berisi angka, tanda plus (+), dan spasi.',
            'username.regex'   => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'alamat.max'       => 'Alamat maksimal 500 karakter.',
            'password.max'     => 'Password maksimal 100 karakter.',
        ]);

        $data = [
            'nama_guru' => trim($validated['nama']),
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
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s.,\'\-]+$/u'],
            'nip' => ['nullable', 'string', 'max:30', 'regex:/^[0-9\- ]*$/', Rule::unique('guru', 'nip')->ignore($guru->id_guru, 'id_guru')],
            'email' => ['required', 'email', 'max:100', Rule::unique('guru', 'email')->ignore($guru->id_guru, 'id_guru')],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]*$/'],
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:500',
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', Rule::unique('guru', 'username')->ignore($guru->id_guru, 'id_guru')],
            'password' => 'nullable|string|min:6|max:100|confirmed',
        ], [
            'nama.required'    => 'Nama guru wajib diisi.',
            'nama.max'         => 'Nama guru maksimal 100 karakter.',
            'nama.regex'       => 'Nama guru hanya boleh berisi huruf, spasi, dan gelar (. , \' -). Simbol lain dilarang.',
            'nip.regex'        => 'NIP hanya boleh berisi angka dan tanda hubung.',
            'no_hp.regex'      => 'Nomor HP hanya boleh berisi angka, tanda plus (+), dan spasi.',
            'username.regex'   => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'alamat.max'       => 'Alamat maksimal 500 karakter.',
            'password.max'     => 'Password maksimal 100 karakter.',
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

    /**
     * Jadwal Mengajar Guru (read-only, Senin–Sabtu).
     * Menampilkan apa saja yang diajar guru tersebut,
     * jam ke-berapa, dan keterangan jam (dari–sampai).
     */
    public function jadwalMengajar()
    {
        $guru = null;
        if (session()->has('user_id') && session('user_type') === 'guru') {
            $guru = Guru::find(session('user_id'));
        }

        $jadwals = JadwalMataPelajaran::with(['mataPelajaran', 'kelas', 'guru'])
            ->when($guru, fn($q) => $q->where('id_guru', $guru->id_guru))
            ->orderByRaw("CASE hari 
                WHEN 'Senin' THEN 1 
                WHEN 'Selasa' THEN 2 
                WHEN 'Rabu' THEN 3 
                WHEN 'Kamis' THEN 4 
                WHEN 'Jumat' THEN 5 
                WHEN 'Sabtu' THEN 6 
                ELSE 7 END")
            ->orderBy('jam')
            ->get();

        $jadwalPerHari = [
            'Senin'  => $jadwals->where('hari', 'Senin')->values(),
            'Selasa' => $jadwals->where('hari', 'Selasa')->values(),
            'Rabu'   => $jadwals->where('hari', 'Rabu')->values(),
            'Kamis'  => $jadwals->where('hari', 'Kamis')->values(),
            'Jumat'  => $jadwals->where('hari', 'Jumat')->values(),
            'Sabtu'  => $jadwals->where('hari', 'Sabtu')->values(),
        ];

        $hariMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $hariIni    = $hariMap[date('l')] ?? 'Senin';
        $totalSesi  = $jadwals->count();
        $totalMapel = $jadwals->pluck('id_mapel')->unique()->count();
        $totalKelas = $jadwals->pluck('id_rooms')->unique()->count();

        return view('guru.jadwal.index', compact(
            'guru', 'jadwals', 'jadwalPerHari', 'hariIni',
            'totalSesi', 'totalMapel', 'totalKelas'
        ));
    }
}