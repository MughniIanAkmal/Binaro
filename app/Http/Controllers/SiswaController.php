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

    public function profile()
    {
        $siswa = Siswa::with(['kelas', 'mataPelajaran', 'barcode'])
            ->find(session('user_id'));

        if (!$siswa) {
            return redirect()->route('login')->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        $qrSvg = null;
        if ($siswa->barcode) {
            try {
                $qrSvg = (new \chillerlan\QRCode\QRCode())->render($siswa->barcode->kode_barcode);
            } catch (\Throwable $e) {
                $qrSvg = null;
            }
        }

        return view('siswa.profile', compact('siswa', 'qrSvg'));
    }

    public function updatePassword(Request $request)
    {
        $passwordLama = trim((string) $request->input('password_lama'));
        $passwordBaru = (string) $request->input('password');
        $konfirmasi = (string) $request->input('password_confirmation');

        $fieldErrors = [];

        // 1. Password lama wajib diisi (dicek kecocokannya di bawah)
        if ($passwordLama === '') {
            $fieldErrors['password_lama'] = 'Password lama wajib diisi.';
        }

        // 3. Password baru minimal 6 karakter
        if ($passwordBaru === '') {
            $fieldErrors['password'] = 'Password baru wajib diisi.';
        } elseif (mb_strlen($passwordBaru) < 6) {
            $fieldErrors['password'] = 'Password baru minimal 6 karakter.';
        }

        // 2. Konfirmasi harus sama dengan password baru
        if ($konfirmasi === '') {
            $fieldErrors['password_confirmation'] = 'Konfirmasi password baru wajib diisi.';
        } elseif ($passwordBaru !== '' && $konfirmasi !== $passwordBaru) {
            $fieldErrors['password_confirmation'] = 'Konfirmasi password baru tidak sesuai.';
        }

        if (!empty($fieldErrors)) {
            return back()->withErrors($fieldErrors)->with('open_password', true);
        }

        $siswa = Siswa::find(session('user_id'));
        if (!$siswa) {
            return redirect()->route('login')->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        // 1. Password lama salah
        $stored = trim((string) $siswa->password);

        $cocok = ($passwordLama === $stored)
            || (str_starts_with($stored, '$2y$') && Hash::check($passwordLama, $stored))
            || (md5($passwordLama) === $stored);

        if (!$cocok) {
            return back()
                ->withErrors(['password_lama' => 'Password lama salah. Periksa kembali password Anda saat ini.'])
                ->with('open_password', true);
        }

        // Simpan apa adanya (plain text) agar terlihat di database — tanpa Hash::make, sesuai permintaan.
        $siswa->update(['password' => $passwordBaru]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function updateProfile(Request $request)
    {
        $siswa = Siswa::find(session('user_id'));
        if (!$siswa) {
            return redirect()->route('login')->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:25', 'regex:/^(?=.*[A-Za-z])[A-Za-z ]+$/', Rule::unique('siswa', 'username')->ignore($siswa->id_siswa, 'id_siswa')],
            'email' => ['required', 'string', 'email:rfc', 'max:100', 'regex:/@/', Rule::unique('siswa', 'email')->ignore($siswa->id_siswa, 'id_siswa')],
            'no_hp' => ['nullable', 'string', 'max:12', 'regex:/^[0-9]*$/'],
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:500',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 25 huruf.',
            'username.regex' => 'Username hanya boleh berisi huruf (A-Z) dan spasi, tanpa angka atau simbol.',
            'username.unique' => 'Username sudah dipakai siswa lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus valid dan mengandung tanda @, contoh: nama@email.com.',
            'email.regex' => 'Email harus mengandung tanda @, contoh: nama@email.com.',
            'email.unique' => 'Email sudah dipakai siswa lain.',
            'no_hp.max' => 'No. HP maksimal 12 angka.',
            'no_hp.regex' => 'No. HP hanya boleh berisi angka (0-9).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_edit', true);
        }

        $siswa->update($validator->validated());

        return back()->with('success', 'Keterangan berhasil diperbarui.');
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
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s.,\'\-]+$/u'],
            'nis' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', 'unique:siswa,nisn'],
            'email' => 'required|email|max:100|unique:siswa,email',
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]*$/'],
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:500',
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', 'unique:siswa,username'],
            'password' => 'required|string|min:6|max:100|confirmed',
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ], [
            'nama.required'   => 'Nama siswa wajib diisi.',
            'nama.max'        => 'Nama siswa maksimal 100 karakter.',
            'nama.regex'      => 'Nama siswa hanya boleh berisi huruf, spasi, dan tanda baca (. , \' -).',
            'nis.regex'       => 'NISN hanya boleh berisi angka.',
            'no_hp.regex'     => 'Nomor HP hanya boleh berisi angka, tanda plus (+), dan spasi.',
            'username.regex'  => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'alamat.max'      => 'Alamat maksimal 500 karakter.',
            'password.max'    => 'Password maksimal 100 karakter.',
        ]);

        $data = [
            'nm_siswa' => trim($validated['nama']),
            'nisn' => trim($validated['nis']),
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
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s.,\'\-]+$/u'],
            'nis' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', Rule::unique('siswa', 'nisn')->ignore($siswa->id_siswa, 'id_siswa')],
            'email' => ['required', 'email', 'max:100', Rule::unique('siswa', 'email')->ignore($siswa->id_siswa, 'id_siswa')],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]*$/'],
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:500',
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', Rule::unique('siswa', 'username')->ignore($siswa->id_siswa, 'id_siswa')],
            'password' => 'nullable|string|min:6|max:100|confirmed',
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ], [
            'nama.required'   => 'Nama siswa wajib diisi.',
            'nama.max'        => 'Nama siswa maksimal 100 karakter.',
            'nama.regex'      => 'Nama siswa hanya boleh berisi huruf, spasi, dan tanda baca (. , \' -).',
            'nis.regex'       => 'NISN hanya boleh berisi angka.',
            'no_hp.regex'     => 'Nomor HP hanya boleh berisi angka, tanda plus (+), dan spasi.',
            'username.regex'  => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'alamat.max'      => 'Alamat maksimal 500 karakter.',
            'password.max'    => 'Password maksimal 100 karakter.',
        ]);

        $data = [
            'nm_siswa' => trim($validated['nama']),
            'nisn' => trim($validated['nis']),
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