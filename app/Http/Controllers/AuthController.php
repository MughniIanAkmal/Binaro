<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('user_id')) {
            return redirect('/rpp');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Coba autentikasi sebagai Admin
        $input = trim($request->username);
        $admin = Admin::where('nip', $input)
            ->orWhere('id_admin', $input)
            ->first();

        if ($admin && (Hash::check($request->password, $admin->password) || $admin->password === $request->password)) {
            if ($admin->password === $request->password) {
                $admin->password = Hash::make($request->password);
                $admin->save();
            }
            Session::put([
                'user_id' => $admin->id_admin,
                'user_type' => 'admin',
                'user_name' => $admin->nama_admin ?? $admin->nama ?? 'Administrator',
            ]);
            return redirect('/rpp')->with('success', 'Selamat datang, ' . ($admin->nama_admin ?? $admin->nama));
        }

        // Coba autentikasi sebagai Guru
        $guru = Guru::where('nip', $input)
            ->orWhere('username', $input)
            ->first();

        if ($guru && (Hash::check($request->password, $guru->password) || $guru->password === $request->password)) {
            if ($guru->password === $request->password) {
                $guru->password = Hash::make($request->password);
                $guru->save();
            }
            Session::put([
                'user_id' => $guru->id_guru,
                'user_type' => 'guru',
                'user_name' => $guru->nama_guru ?? $guru->nama,
            ]);
            return redirect('/rpp')->with('success', 'Selamat datang, ' . ($guru->nama_guru ?? $guru->nama));
        }

        return back()->with('error', 'NIP / Username atau Password salah.');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Anda telah keluar.');
    }
}