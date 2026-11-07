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
                // Abaikan error format hash
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
        $namaLengkap = isset($validated['nama_lengkap']) ? trim($validated['nama_lengkap']) : null;

        // Rate limiting untuk keamanan brute force
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
            // Jika user mengisi nama lengkap, pastikan cocok atau mirip (opsional, toleran)
            if (!empty($namaLengkap)) {
                $dbName = strtolower(trim($siswa->nm_siswa));
                $inputName = strtolower($namaLengkap);
                // Cek kesamaan jika nama diisi
                if (!str_contains($dbName, $inputName) && !str_contains($inputName, $dbName)) {
                    return $this->error('Nama lengkap tidak sesuai dengan data NISN terdaftar.', 401);
                }
            }

            RateLimiter::clear($throttleKey);

            // Buat token sederhana / Sanctum jika tersedia
            $token = bin2hex(random_bytes(32));

            return $this->success([
                'token' => $token,
                'role' => 'siswa',
                'user' => [
                    'id_siswa' => $siswa->id_siswa,
                    'nisn' => $siswa->nisn,
                    'nm_siswa' => $siswa->nm_siswa,
                    'email' => $siswa->email,
                    'no_hp' => $siswa->no_hp,
                    'kelas' => $siswa->nama_kelas,
                    'foto_profil' => $siswa->foto_profil,
                ]
            ], 'Login berhasil! Selamat datang, ' . $siswa->nm_siswa);
        }

        // 2. Fallback cek Guru (jika NIP dimasukkan di kolom NISN)
        $guru = Guru::where('nip', $nisn)->orWhere('username', $nisn)->first();
        if ($guru && $this->verifyPassword($password, $guru->password)) {
            RateLimiter::clear($throttleKey);
            $token = bin2hex(random_bytes(32));

            return $this->success([
                'token' => $token,
                'role' => 'guru',
                'user' => [
                    'id_guru' => $guru->id_guru,
                    'nip' => $guru->nip,
                    'nama_guru' => $guru->nama_guru,
                    'email' => $guru->email,
                ]
            ], 'Login guru berhasil! Selamat datang, ' . $guru->nama_guru);
        }

        RateLimiter::hit($throttleKey, 60);

        return $this->error('NISN atau Password salah.', 401);
    }

    /**
     * API Logout
     */
    public function logout(Request $request)
    {
        return $this->success(null, 'Berhasil logout');
    }
}

