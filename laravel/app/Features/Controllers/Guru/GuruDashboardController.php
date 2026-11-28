<?php

namespace App\Features\Controllers\Guru;

use App\Features\Controllers\Controller;
use App\Features\Models\Guru;
use App\Features\Models\Jadwal;
use App\Features\Models\MataPelajaran;
use App\Features\Models\Pr;
use Carbon\Carbon;

class GuruDashboardController extends Controller
{
    private const HARI_INDONESIA = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    public function index()
    {
        if (session('user_type') !== 'guru') {
            return redirect()->route('login');
        }

        $guru = Guru::find(session('user_id'));
        if (! $guru) {
            session()->flush();

            return redirect()->route('login')->with('error', 'Sesi guru tidak valid. Silakan login kembali.');
        }

        $prAktif = Pr::where(function ($q) {
            $q->whereNull('tgl_tenggat')->orWhere('tgl_tenggat', '>=', Carbon::today());
        })->count();

        $prs = Pr::with('mataPelajaran')->latest('id_pr')->take(2)->get();

        $hariIni = self::HARI_INDONESIA[now()->format('l')] ?? now()->format('l');
        $jadwalHariIni = Jadwal::where('id_guru', $guru->id_guru)
            ->where('hari', $hariIni)
            ->orderBy('jam')
            ->get();
        $mapelById = MataPelajaran::whereIn('id_mapel', $jadwalHariIni->pluck('id_mapel'))
            ->pluck('nama_mapel', 'id_mapel');

        return view('guru.dashboard', compact(
            'guru',
            'prAktif',
            'prs',
            'jadwalHariIni',
            'mapelById',
        ));
    }
}
