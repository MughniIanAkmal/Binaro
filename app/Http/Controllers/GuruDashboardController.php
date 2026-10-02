<?php

namespace App\Http\Controllers;

use App\Models\Guru;

class GuruDashboardController extends Controller
{
    public function index()
    {
        if (session('user_type') !== 'guru') {
            return redirect()->route('login');
        }

        $guru = Guru::find(session('user_id'));
        if (!$guru) {
            session()->flush();

            return redirect()->route('login')->with('error', 'Sesi guru tidak valid. Silakan login kembali.');
        }

        return view('guru.dashboard', compact('guru'));
    }
}