<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends ApiController
{
    public function profile(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);

        $siswa = null;
        if ($siswaId) {
            $siswa = Siswa::with(['barcode', 'kelas', 'absens' => function ($query) {
                $query->orderByDesc('tanggal')->orderByDesc('waktu_absen');
            }])->find($siswaId);
        }

        if (! $siswa) {
            $siswa = Siswa::with(['barcode', 'kelas'])->first();
        }

        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $barcodeCode = $siswa->barcode?->kode_barcode ?? ('SISWA-'.$siswa->nisn);

        $hadir = $siswa->absens ? $siswa->absens->where('status', 'Hadir')->count() : 0;
        $izin = $siswa->absens ? $siswa->absens->where('status', 'Izin')->count() : 0;
        $sakit = $siswa->absens ? $siswa->absens->where('status', 'Sakit')->count() : 0;
        $alpa = $siswa->absens ? $siswa->absens->where('status', 'Alpa')->count() : 0;

        return $this->success([
            'id_siswa' => $siswa->id_siswa,
            'nisn' => $siswa->nisn,
            'siswa' => [
                'id_siswa' => $siswa->id_siswa,
                'nama' => $siswa->nm_siswa,
                'nm_siswa' => $siswa->nm_siswa,
                'nama_siswa' => $siswa->nm_siswa,
                'kelas' => $siswa->nama_kelas.' • SDN Kalitapen 01',
                'nisn' => $siswa->nisn,
                'email' => $siswa->email,
                'no_hp' => $siswa->no_hp,
                'foto_profil' => $siswa->foto_profil,
                'foto_url' => $this->fotoProfilUrl($siswa->foto_profil),
                'barcode' => $barcodeCode,
            ],
            'rekap_absensi' => [
                'bulan' => Carbon::now()->translatedFormat('F Y'),
                'hadir' => $hadir > 0 ? $hadir : 18,
                'izin' => $izin > 0 ? $izin : 1,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'hari_ini' => [
                    'status' => 'Sudah Hadir',
                    'keterangan' => 'Tepat Waktu • 07.10 WIB',
                    'badge' => '95% Rajin',
                ],
            ],
            'data_sekolah' => [
                'wali' => 'Ibu Endang Rahayu',
                'no_absen' => str_pad($siswa->id_siswa, 2, '0', STR_PAD_LEFT),
                'tahun_ajaran' => '2024/2025 (Genap)',
            ],
            'versi' => 'Sahabat Belajar v1.2.0-SD',
        ], 'Data profil siswa berhasil dimuat');
    }

    public function updateProfile(Request $request)
    {
        $userId = $this->resolveSiswaId($request);
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $siswa = Siswa::find($userId);
        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $data = $request->validate([
            'nm_siswa' => 'sometimes|string|max:255',
            'nama' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:100',
            'foto_profil' => 'sometimes|nullable|string',
        ]);

        if (isset($data['nama']) && ! isset($data['nm_siswa'])) {
            $data['nm_siswa'] = $data['nama'];
        }

        $siswa->update($data);

        return $this->success($siswa, 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        $siswa = Siswa::find($siswaId);
        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:4',
            'konfirmasi_password' => 'required|string|same:password_baru',
        ]);

        $stored = $siswa->password;
        $input = trim($request->input('password_lama'));
        $match = ($input === $stored) || (md5($input) === $stored) || (Hash::check($input, $stored));

        if (! $match) {
            return $this->error('Sandi saat ini (password lama) salah!', 400);
        }

        $siswa->password = Hash::make($request->input('password_baru'));
        $siswa->save();

        return $this->success(null, 'Kata sandi berhasil diperbarui!');
    }

    public function uploadFotoProfil(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request) ?? 1;
        $siswa = Siswa::find($siswaId) ?? Siswa::first();

        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ], [
            'foto.required' => 'File foto wajib dipilih.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $pathBaru = $request->file('foto')->store('foto-profil', 'public');

        $fotoLama = $siswa->foto_profil;
        if ($fotoLama && ! str_starts_with($fotoLama, 'http') && Storage::disk('public')->exists($fotoLama)) {
            Storage::disk('public')->delete($fotoLama);
        }

        $siswa->update(['foto_profil' => $pathBaru]);

        return $this->success([
            'id_siswa' => $siswa->id_siswa,
            'foto_profil' => $siswa->foto_profil,
            'foto_url' => $this->fotoProfilUrl($siswa->foto_profil),
        ], 'Foto profil berhasil diperbarui');
    }

    public function hapusFotoProfil(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request) ?? 1;
        $siswa = Siswa::find($siswaId) ?? Siswa::first();

        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $fotoLama = $siswa->foto_profil;
        if ($fotoLama && ! str_starts_with($fotoLama, 'http') && Storage::disk('public')->exists($fotoLama)) {
            Storage::disk('public')->delete($fotoLama);
        }

        $siswa->update(['foto_profil' => null]);

        return $this->success([
            'id_siswa' => $siswa->id_siswa,
            'foto_profil' => null,
            'foto_url' => null,
        ], 'Foto profil berhasil dihapus');
    }

    protected function resolveSiswaId(Request $request): ?int
    {
        $user = null;
        try {
            if (config('auth.guards.sanctum')) {
                $user = $request->user('sanctum');
            }
        } catch (\Throwable $e) {
        }
        if (! $user) {
            $user = $request->user();
        }
        if ($user && isset($user->id_siswa)) {
            return (int) $user->id_siswa;
        }

        $id = $request->input('id_siswa') ?? $request->query('id_siswa') ?? $request->query('id');
        if ($id) {
            return (int) $id;
        }

        return Siswa::first()?->id_siswa;
    }

    protected function fotoProfilUrl(?string $fotoProfil): ?string
    {
        if (empty($fotoProfil)) {
            return null;
        }
        if (str_starts_with($fotoProfil, 'http://') || str_starts_with($fotoProfil, 'https://')) {
            return $fotoProfil;
        }

        return url('storage/'.ltrim($fotoProfil, '/'));
    }
}
