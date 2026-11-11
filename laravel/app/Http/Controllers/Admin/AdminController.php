<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\AbsensiSetting;
use App\Models\Admin;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;

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
        $totalJadwal = class_exists(Jadwal::class) ? Jadwal::count() : 0;

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
        $absensiSetting = class_exists(AbsensiSetting::class) ? AbsensiSetting::getSettings() : null;

        return view('admin.dashboard', compact('admin', 'stats', 'recentGuru', 'recentSiswa', 'absensiSetting'));
    }
}
