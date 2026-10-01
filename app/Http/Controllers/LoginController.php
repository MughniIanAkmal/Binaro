<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'role' => 'required|in:siswa,guru,admin',
            'username' => 'required',
            'identity' => 'required',
            'password' => 'required',
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOGIN SISWA
        |--------------------------------------------------------------------------
        */

        if ($request->role === 'siswa') {

            $siswa = DB::table('siswa')
                ->where('nm_siswa', $request->username)
                ->where('nisn', $request->identity)
                ->first();

            if (!$siswa) {
                return back()
                    ->withErrors([
                        'username' => 'Nama siswa atau NISN/NIS tidak sesuai.'
                    ])
                    ->withInput();
            }

            if (!Hash::check($request->password, $siswa->password)) {
                return back()
                    ->withErrors([
                        'password' => 'Password salah.'
                    ])
                    ->withInput();
            }

            session([
                'login' => true,
                'role' => 'siswa',
                'id_user' => $siswa->id_siswa,
                'nama' => $siswa->nm_siswa,
                'nis' => $siswa->nisn,
                'id_mapel' => $siswa->id_mapel,
                'id_rooms' => $siswa->id_rooms,
            ]);

            return redirect()->route('beranda');
        }


        /*
        |--------------------------------------------------------------------------
        | ROLE GURU
        |--------------------------------------------------------------------------
        */

        if ($request->role === 'guru') {

            $guru = DB::table('guru')
                ->where('nama_guru', $request->username)
                ->where('nip', $request->identity)
                ->first();

            if (!$guru) {
                return back()
                    ->withErrors([
                        'username' => 'Nama guru atau NIP tidak sesuai.'
                    ])
                    ->withInput();
            }

            if (!Hash::check($request->password, $guru->password)) {
                return back()
                    ->withErrors([
                        'password' => 'Password salah.'
                    ])
                    ->withInput();
            }

            session([
                'login' => true,
                'role' => 'guru',
                'id_user' => $guru->id_guru,
                'nama' => $guru->nama_guru,
                'nip' => $guru->nip,
            ]);

            return redirect()->route('guru.dashboard');
        }


        /*
        |--------------------------------------------------------------------------
        | ROLE ADMIN
        |--------------------------------------------------------------------------
        */

        if ($request->role === 'admin') {

            return back()->withErrors([
                'role' => 'Login Admin belum dikonfigurasi.'
            ]);
        }


        return back()->withErrors([
            'role' => 'Role tidak valid.'
        ]);
    }


    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect()->route('login');
    }
}