<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (session()->has('user_id')) {
            return match (session('user_type')) {
                'admin' => redirect()->route('admin.dashboard'),
                'siswa' => redirect()->route('siswa.dashboard'),
                'guru' => redirect()->route('guru.dashboard'),
                default => redirect()->route('login'),
            };
        }

        return view('login');
    }

    public function login(Request $request, AuthController $authController)
    {
        $validated = $request->validate([
            'role' => 'nullable|in:siswa,guru,admin',
            'username' => 'nullable|string|max:100',
            'identity' => 'nullable|string|max:100',
            'password' => 'required|string|max:100',
        ]);

        $role = $validated['role'] ?? null;

        // Ambil identifier dari salah satu field (toleran: username ATAU identity).
        // ConvertEmptyStringsToNull mengubah "" menjadi null, jadi pakai null-coalescing + trim.
        $identity = isset($validated['identity']) ? trim((string) $validated['identity']) : '';
        $username = isset($validated['username']) ? trim((string) $validated['username']) : '';
        $identifier = $identity !== '' ? $identity : $username;

        if ($identifier === '') {
            $field = in_array($role, ['siswa', 'guru'], true) || $role === null
                ? ($role === 'admin' ? 'username' : 'identity')
                : 'username';

            return back()
                ->withErrors([$field => $role === 'admin'
                    ? 'Nama / NIP Admin wajib diisi.'
                    : 'NISN / NIP wajib diisi.'])
                ->withInput();
        }

        $request->merge(['username' => $identifier]);

        return $authController->login($request);
    }
}