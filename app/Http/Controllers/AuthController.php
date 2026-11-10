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
        $admin = Admin::where('nip', $request->username)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            Session::put([
                'user_id' => $admin->id_admin,
                'user_type' => 'admin',
                'user_name' => $admin->nama_admin,
            ]);
            return redirect('/rpp')->with('success', 'Selamat datang, ' . $admin->nama_admin);
        }

        // Coba autentikasi sebagai Guru
        $guru = Guru::where('nip', $request->username)->first();
        if ($guru && Hash::check($request->password, $guru->password)) {
            Session::put([
                'user_id' => $guru->id_guru,
                'user_type' => 'guru',
                'user_name' => $guru->nama_guru,
            ]);
            return redirect('/rpp')->with('success', 'Selamat datang, ' . $guru->nama_guru);
        }

        return back()->with('error', 'NIP / Username atau Password salah.');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Anda telah keluar.');
    }
}