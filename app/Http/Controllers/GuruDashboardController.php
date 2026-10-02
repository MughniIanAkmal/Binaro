<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Pr;
use Carbon\Carbon;

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

        $totalMapel = MataPelajaran::count();
        $totalMateri = Materi::count();
        $totalPr = Pr::count();
        $prAktif = Pr::where(function ($q) {
            $q->whereNull('tgl_tenggat')->orWhere('tgl_tenggat', '>=', Carbon::today());
        })->count();

        $mapels = MataPelajaran::withCount('bab')->latest('id_mapel')->take(3)->get();
        $prs = Pr::with('mataPelajaran')->latest('id_pr')->take(2)->get();

        return view('guru.dashboard', compact('guru', 'totalMapel', 'totalMateri', 'totalPr', 'prAktif', 'mapels', 'prs'));
    }
}