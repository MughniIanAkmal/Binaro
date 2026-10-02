<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('user_id')) {
            $type = session('user_type');
            if ($type === 'admin') return redirect('/admin/dashboard');
            if ($type === 'siswa') return redirect('/siswa/dashboard');
            return redirect('/guru/dashboard');
        }
        return view('auth.login');
    }

    private function verifyPassword(string $inputPassword, ?string $storedPassword): bool
    {
        if ($storedPassword === null || $storedPassword === '') return false;
        $trimmedInput = trim($inputPassword);
        $trimmedStored = trim($storedPassword);

        // 1. Plain-text exact match
        if ($trimmedInput === $trimmedStored) {
            return true;
        }

        // 2. Demo and shortcut aliases
        $lowIn = strtolower($trimmedInput);
        $lowSt = strtolower($trimmedStored);
        if ($lowSt === 'admin123' && in_array($lowIn, ['admin', 'admin123'])) return true;
        if ($lowSt === 'admin' && in_array($lowIn, ['admin', 'admin123'])) return true;
        if ($lowSt === 'guru123' && in_array($lowIn, ['guru', 'guru123'])) return true;
        if ($lowSt === 'guru' && in_array($lowIn, ['guru', 'guru123'])) return true;
        if ($lowSt === 'siswa123' && in_array($lowIn, ['siswa', 'siswa123'])) return true;
        if ($lowSt === 'siswa' && in_array($lowIn, ['siswa', 'siswa123'])) return true;

        // 3. MD5 match
        if (md5($trimmedInput) === $trimmedStored) {
            return true;
        }

        // 4. Safe Hash check (only if valid hash format to prevent BcryptHasher RuntimeException)
        if (str_starts_with($trimmedStored, '$2y$') || str_starts_with($trimmedStored, '$2a$') || str_starts_with($trimmedStored, '$argon2')) {
            try {
                if (Hash::check($trimmedInput, $trimmedStored)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Ignore hash checking failures
            }
        }

        return false;
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:100',
            'role' => 'nullable|in:siswa,guru,admin',
        ]);

        $selectedRole = $request->input('role');
        $rawUsername = trim($request->input('username'));
        $password = trim($request->input('password'));
        $lowerUsername = strtolower($rawUsername);
        $len = strlen($rawUsername);

        $throttleKey = Str::transliterate($lowerUsername.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.");
        }

        // 1. Siswa Check (10 Digit NISN)
        if (($selectedRole === null || $selectedRole === 'siswa') && $len === 10 && ctype_digit($rawUsername)) {
            $siswa = Siswa::where('nisn', $rawUsername)->first();
            if ($siswa && $this->verifyPassword($password, $siswa->password)) {
                RateLimiter::clear($throttleKey);
                Session::put([
                    'user_id' => $siswa->id_siswa,
                    'user_type' => 'siswa',
                    'user_name' => $siswa->nm_siswa,
                ]);
                return redirect('/siswa/dashboard')->with('success', 'Selamat datang, ' . $siswa->nm_siswa);
            }
        }

        // 2. Guru Check (18 Digit NIP)
        if (($selectedRole === null || $selectedRole === 'guru') && $len === 18 && ctype_digit($rawUsername)) {
            $guru = Guru::where('nip', $rawUsername)->first();
            if ($guru && $this->verifyPassword($password, $guru->password)) {
                RateLimiter::clear($throttleKey);
                Session::put([
                    'user_id' => $guru->id_guru,
                    'user_type' => 'guru',
                    'user_name' => $guru->nama_guru,
                ]);
                return redirect('/guru/dashboard')->with('success', 'Selamat datang, ' . $guru->nama_guru);
            }
        }

        // 3. Admin Check (NIP, nama_admin, or username / alias)
        $admin = null;
        if ($selectedRole === null || $selectedRole === 'admin') {
            $admin = Admin::where('nip', $rawUsername)
                          ->orWhere('nama_admin', $rawUsername)
                          ->orWhereRaw('LOWER(nip) = ?', [$lowerUsername])
                          ->orWhereRaw('LOWER(nama_admin) = ?', [$lowerUsername])
                          ->first();

            if (!$admin && in_array($lowerUsername, ['admin', 'administrator', 'admin utama'])) {
                $admin = Admin::first();
            }
        }

        if ($admin && $this->verifyPassword($password, $admin->password)) {
            RateLimiter::clear($throttleKey);
            Session::put([
                'user_id' => $admin->id_admin,
                'user_type' => 'admin',
                'user_name' => $admin->nama_admin,
            ]);
            return redirect('/admin/dashboard')->with('success', 'Selamat datang, ' . $admin->nama_admin);
        }

        // 4. Guru Flexible Check (nip, username, email, nama_guru, or shortcut)
        $guru = null;
        if ($selectedRole === null || $selectedRole === 'guru') {
            $guru = Guru::where('nip', $rawUsername)
                        ->orWhere('username', $rawUsername)
                        ->orWhere('email', $rawUsername)
                        ->orWhereRaw('LOWER(nip) = ?', [$lowerUsername])
                        ->orWhereRaw('LOWER(username) = ?', [$lowerUsername])
                        ->orWhereRaw('LOWER(email) = ?', [$lowerUsername])
                        ->first();

            if (!$guru && in_array($lowerUsername, ['guru', 'guru demo', 'pengajar'])) {
                $guru = Guru::first();
            }
        }

        if ($guru && $this->verifyPassword($password, $guru->password)) {
            RateLimiter::clear($throttleKey);
            Session::put([
                'user_id' => $guru->id_guru,
                'user_type' => 'guru',
                'user_name' => $guru->nama_guru,
            ]);
            return redirect('/guru/dashboard')->with('success', 'Selamat datang, ' . $guru->nama_guru);
        }

        // 5. Siswa Flexible Check (nisn, username, email, nm_siswa, or shortcut)
        $siswa = null;
        if ($selectedRole === null || $selectedRole === 'siswa') {
            $siswa = Siswa::where('nisn', $rawUsername)
                          ->orWhere('username', $rawUsername)
                          ->orWhere('email', $rawUsername)
                          ->orWhereRaw('LOWER(nisn) = ?', [$lowerUsername])
                          ->orWhereRaw('LOWER(username) = ?', [$lowerUsername])
                          ->orWhereRaw('LOWER(email) = ?', [$lowerUsername])
                          ->first();

            if (!$siswa && in_array($lowerUsername, ['siswa', 'siswa demo', 'murid'])) {
                $siswa = Siswa::first();
            }
        }

        if ($siswa && $this->verifyPassword($password, $siswa->password)) {
            RateLimiter::clear($throttleKey);
            Session::put([
                'user_id' => $siswa->id_siswa,
                'user_type' => 'siswa',
                'user_name' => $siswa->nm_siswa,
            ]);
            return redirect('/siswa/dashboard')->with('success', 'Selamat datang, ' . $siswa->nm_siswa);
        }

        RateLimiter::hit($throttleKey, 900);

        return back()->with('error', 'NISN / NIP / Username atau Password salah.');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Anda telah keluar.');
    }
}
