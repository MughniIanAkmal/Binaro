<?php

namespace App\Features\Controllers\Admin;

use App\Features\Controllers\Controller;
use App\Features\Models\Absen;
use App\Features\Models\Admin;
use App\Features\Models\Guru;
use App\Features\Models\MataPelajaran;
use App\Features\Models\Rpp;
use App\Features\Models\Siswa;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = null;
        if (session()->has('user_id') && session('user_type') === 'admin') {
            $admin = Admin::find(session('user_id'));
        }

        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalMapel = MataPelajaran::count();

        $today = now()->toDateString();
        $hadirHariIni = Absen::where('tanggal', $today)->where('status', 'Hadir')->count();
        $izinSakitHariIni = Absen::where('tanggal', $today)->whereIn('status', ['Izin', 'Sakit'])->count();
        $alpaHariIni = max(0, $totalSiswa - ($hadirHariIni + $izinSakitHariIni));
        $persentaseHadir = $totalSiswa > 0 ? round(($hadirHariIni / $totalSiswa) * 100, 1) : 0;

        $rppPerluReview = Rpp::whereIn('status', ['menunggu_review', 'draft', 'perlu_revisi'])->count();

        $stats = [
            'total_siswa' => $totalSiswa,
            'total_guru' => $totalGuru,
            'total_mapel' => $totalMapel,
            'hadir_hari_ini' => $hadirHariIni,
            'izin_sakit_hari_ini' => $izinSakitHariIni,
            'alpa_hari_ini' => $alpaHariIni,
            'persentase_hadir' => $persentaseHadir,
            'rpp_perlu_review' => $rppPerluReview,
        ];

        $recentGuru = Guru::latest()->take(5)->get();
        $recentSiswa = Siswa::with('kelas')->latest()->take(5)->get();

        return view('admin.dashboard', compact('admin', 'stats', 'recentGuru', 'recentSiswa'));
    }
}
