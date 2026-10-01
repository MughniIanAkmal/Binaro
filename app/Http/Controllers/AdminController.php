<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Http\Request;

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
        $totalKelas = Kelas::count();
        $totalJadwal = class_exists(\App\Models\Jadwal::class) ? \App\Models\Jadwal::count() : 0;

        $today = now()->toDateString();
        $hadirHariIni = Absen::where('tanggal', $today)->where('status', 'Hadir')->count();
        $izinSakitHariIni = Absen::where('tanggal', $today)->whereIn('status', ['Izin', 'Sakit'])->count();
        $persentaseHadir = $totalSiswa > 0 ? round(($hadirHariIni / $totalSiswa) * 100, 1) : 0;

        $stats = [
            'total_siswa' => $totalSiswa,
            'total_guru' => $totalGuru,
            'total_mapel' => $totalMapel,
            'total_kelas' => $totalKelas,
            'total_jadwal' => $totalJadwal,
            'hadir_hari_ini' => $hadirHariIni,
            'izin_sakit_hari_ini' => $izinSakitHariIni,
            'persentase_hadir' => $persentaseHadir,
        ];

        $recentGuru = Guru::latest()->take(5)->get();
        $recentSiswa = Siswa::with('kelas')->latest()->take(5)->get();
        $absensiSetting = class_exists(\App\Models\AbsensiSetting::class) ? \App\Models\AbsensiSetting::getSettings() : null;

        return view('admin.dashboard', compact('admin', 'stats', 'recentGuru', 'recentSiswa', 'absensiSetting'));
    }
}
