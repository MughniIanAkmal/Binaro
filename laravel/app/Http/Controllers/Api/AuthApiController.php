<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthApiController extends ApiController
{
    /**
     * Upgrade legacy password (plain or MD5) to bcrypt after successful login.
     */
    private function upgradeLegacyPassword($user, string $plainPassword): void
    {
        $stored = (string) ($user->password ?? '');
        if (! str_starts_with($stored, '$2y$') && ! str_starts_with($stored, '$2a$') && ! str_starts_with($stored, '$argon2')) {
            $user->update(['password' => Hash::make($plainPassword)]);
        }
    }

    /**
     * Memverifikasi kecocokan password (mendukung Bcrypt, MD5, dan plain text)
     */
    private function verifyPassword(string $inputPassword, ?string $storedPassword): bool
    {
        if ($storedPassword === null || $storedPassword === '') {
            return false;
        }
        $trimmedInput = trim($inputPassword);
        $trimmedStored = trim($storedPassword);

        // 1. Plain text match
        if ($trimmedInput === $trimmedStored) {
            return true;
        }

        // 1b. Toleransi seeder default: jika guru demo, izinkan guru123 dan guru1234
        if (in_array($trimmedInput, ['guru123', 'guru1234']) && in_array($trimmedStored, ['guru123', 'guru1234'])) {
            return true;
        }

        // 2. MD5 match
        if (md5($trimmedInput) === $trimmedStored) {
            return true;
        }

        // 3. Hash check (Bcrypt / Argon)
        if (str_starts_with($trimmedStored, '$2y$') || str_starts_with($trimmedStored, '$2a$') || str_starts_with($trimmedStored, '$argon2')) {
            try {
                if (Hash::check($trimmedInput, $trimmedStored)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return false;
    }

    /**
     * API Login untuk Siswa (dan Guru/Admin jika diperlukan)
     * Menerima: nisn, password, dan nama_lengkap (opsional/verifikasi)
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'nisn' => 'required|string',
            'password' => 'required|string',
            'nama_lengkap' => 'nullable|string',
        ]);
        $nisn = trim($validated['nisn']);
        $password = trim($validated['password']);
        $namaLengkap = $validated['nama_lengkap'] ?? null;

        $throttleKey = Str::transliterate($nisn . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->error("Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.", 429);
        }

        // 1. Cari data Siswa berdasarkan NISN / Username
        $siswa = Siswa::with('kelas')
            ->where('nisn', $nisn)
            ->orWhere('username', $nisn)
            ->first();
        if ($siswa && $this->verifyPassword($password, $siswa->password)) {
            if (!empty($namaLengkap)) {
                $dbName = strtolower(trim($siswa->nm_siswa));
                $inputName = strtolower($namaLengkap);
                if (!str_contains($dbName, $inputName) && !str_contains($inputName, $dbName)) {
                    return $this->error('Nama lengkap tidak sesuai dengan data NISN terdaftar.', 401);
                }
            }
            RateLimiter::clear($throttleKey);
            $this->upgradeLegacyPassword($siswa, $password);
            $token = method_exists($siswa, 'createToken') ? $siswa->createToken('binaro-app')->plainTextToken : bin2hex(random_bytes(32));

            return $this->success([
                'token' => $token,
                'role' => 'siswa',
                'user' => [
                    'id_siswa' => $siswa->id_siswa,
                    'nisn' => $siswa->nisn,
                    'nm_siswa' => $siswa->nm_siswa,
                    'nama_siswa' => $siswa->nm_siswa,
                    'nama' => $siswa->nm_siswa,
                    'email' => $siswa->email,
                    'no_hp' => $siswa->no_hp,
                    'kelas' => $siswa->nama_kelas,
                    'foto_profil' => $siswa->foto_profil,
                ],
            ], "Login berhasil! Selamat datang, {$siswa->nm_siswa}");
        }

        // 2. Fallback cek Guru
        $guru = Guru::where('nip', $nisn)->orWhere('username', $nisn)->first();
        if ($guru && $this->verifyPassword($password, $guru->password)) {
            RateLimiter::clear($throttleKey);
            $this->upgradeLegacyPassword($guru, $password);
            $token = method_exists($guru, 'createToken') ? $guru->createToken('binaro-app')->plainTextToken : bin2hex(random_bytes(32));

            return $this->success([
                'token' => $token,
                'role' => 'guru',
                'user' => [
                    'id_guru' => $guru->id_guru,
                    'nip' => $guru->nip,
                    'nama_guru' => $guru->nama_guru,
                    'email' => $guru->email,
                ],
            ], "Login guru berhasil! Selamat datang, {$guru->nama_guru}");
        }

        // 3. Fallback cek Admin
        $admin = Admin::where('nip', $nisn)->first();
        if ($admin && $this->verifyPassword($password, $admin->password)) {
            RateLimiter::clear($throttleKey);
            $this->upgradeLegacyPassword($admin, $password);
            $token = method_exists($admin, 'createToken') ? $admin->createToken('binaro-app')->plainTextToken : bin2hex(random_bytes(32));

            return $this->success([
                'token' => $token,
                'role' => 'admin',
                'user' => [
                    'id_admin' => $admin->id_admin,
                    'nip' => $admin->nip,
                    'nama_admin' => $admin->nama_admin,
                    'email' => $admin->email ?? null,
                ]
            ], 'Login admin berhasil! Selamat datang, ' . $admin->nama_admin);
        }

        RateLimiter::hit($throttleKey, 60);
        return $this->error('NISN atau Password salah.', 401);
    }

    /**
     * API Logout — hanya mencabut token yang dipakai pada request ini.
     * Token lain milik user yang sama tetap berlaku.
     */
    public function logout(Request $request)
    {
        $user = null;
        try {
            if (config('auth.guards.sanctum')) {
                $user = $request->user('sanctum');
            }
        } catch (\Throwable $e) {}
        if (! $user) {
            $user = $request->user();
        }
        if ($user && method_exists($user, 'currentAccessToken')) {
            $user->currentAccessToken()?->delete();
        }

        return $this->success(null, 'Berhasil logout');
    }
}
