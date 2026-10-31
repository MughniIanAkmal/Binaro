<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\StoreSiswaRequest;
use App\Http\Requests\Siswa\UpdateSiswaRequest;
use App\Models\Barcode;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Notifikasi;
use App\Models\Siswa;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function dashboard()
    {
        $siswa = Siswa::with(['kelas', 'mataPelajaran'])->find(session('user_id'));
        if (! $siswa) {
            session()->flush();

            return redirect()->route('login')->with('error', 'Sesi siswa tidak valid. Silakan login kembali.');
        }

        $nama = $siswa->nm_siswa;
        $kelas = $siswa->kelas?->pararel ?? 'Siswa';
        $sekolah = 'SDN Kalitapen 01';

        // Semua mata pelajaran beserta jumlah Bab.
        $daftarMapel = MataPelajaran::withCount('bab')->orderBy('nama_mapel')->get();
        $mapel = $daftarMapel->map(function ($m, $i) {
            return [
                'id_mapel' => $m->id_mapel,
                'nama' => $m->nama_mapel,
                'warna' => $i % 2 ? 'orange' : 'blue',
                'icon' => '▤',
                'badge' => $m->bab_count.' Bab',
                'materi' => $m->deskripsi ?? 'Materi dan kuis pembelajaran',
                'progress' => 0,
            ];
        })->toArray();

        // PR / tugas yang dikirim ke siswa ini (via notifikasi), yang belum lewat tenggat.
        $notifs = Notifikasi::where('id_siswa', $siswa->id_siswa)
            ->with('pr.mataPelajaran')
            ->latest('id_notifikasi')
            ->get();
        $semuaPr = $notifs->pluck('pr')->filter()->unique('id_pr')->values();
        $tugas_total = $semuaPr->count();
        $tugas = $semuaPr
            ->filter(fn ($pr) => Carbon::parse($pr->tgl_tenggat)->gte(now()->startOfDay()))
            ->sortBy('tgl_tenggat')
            ->take(4)
            ->map(function ($pr, $i) {
                return [
                    'mapel' => $pr->mataPelajaran->nama_mapel ?? '-',
                    'judul' => $pr->nama_pr,
                    'deadline' => Carbon::parse($pr->tgl_tenggat)->translatedFormat('d M Y, H:i'),
                    'warna' => $i % 2 ? 'orange' : 'blue',
                ];
            })->values()->toArray();

        return view('siswa.beranda.index', compact('nama', 'kelas', 'sekolah', 'tugas', 'tugas_total', 'mapel'));
    }

    public function profile()
    {
        $siswa = Siswa::with(['kelas', 'mataPelajaran', 'barcode'])
            ->find(session('user_id'));

        if (! $siswa) {
            return redirect()->route('login')->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        $qrSvg = null;
        if ($siswa->barcode) {
            try {
                $qrSvg = (new QRCode)->render($siswa->barcode->kode_barcode);
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

        if (! empty($fieldErrors)) {
            return back()->withErrors($fieldErrors)->with('open_password', true);
        }

        $siswa = Siswa::find(session('user_id'));
        if (! $siswa) {
            return redirect()->route('login')->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        // 1. Password lama salah
        $stored = trim((string) $siswa->password);

        $cocok = ($passwordLama === $stored)
            || (str_starts_with($stored, '$2y$') && Hash::check($passwordLama, $stored))
            || (md5($passwordLama) === $stored);

        if (! $cocok) {
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
        if (! $siswa) {
            return redirect()->route('login')->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        $validator = Validator::make($request->all(), [
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
            ->when($idRooms, fn ($q) => $q->where('id_rooms', $idRooms))
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

    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();

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

    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        $validated = $request->validated();

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

        if (! empty($validated['password'])) {
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
