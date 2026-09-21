<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Barcode;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedKelas = $request->input('id_rooms');

        $query = Siswa::query()
            ->leftJoin('absen', function ($join) use ($selectedDate) {
                $join->on('siswa.id_siswa', '=', 'absen.id_siswa')
                     ->where('absen.tanggal', '=', $selectedDate);
            })
            ->leftJoin('kelas', 'siswa.id_rooms', '=', 'kelas.id_rooms')
            ->select([
                'siswa.id_siswa',
                'siswa.nm_siswa',
                'siswa.nisn',
                'kelas.pararel as nama_kelas',
                'absen.id_absen',
                'absen.metode',
                DB::raw("COALESCE(absen.status, 'Alpa') as status_kehadiran"),
                'absen.waktu_absen',
                'absen.keterangan',
                'absen.berkas_surat'
            ]);

        if ($selectedKelas) {
            $query->where('siswa.id_rooms', $selectedKelas);
        }

        if ($request->filled('status')) {
            if ($request->status === 'izin_sakit') {
                $query->whereIn(DB::raw("COALESCE(absen.status, 'Alpa')"), ['Izin', 'Sakit']);
            } else {
                $query->where(DB::raw("COALESCE(absen.status, 'Alpa')"), $request->status);
            }
        }

        $siswaList = $query->orderBy('siswa.nm_siswa')->paginate(10)->withQueryString();

        // Hitung KPI
        $totalSiswa = Siswa::when($selectedKelas, fn($q) => $q->where('id_rooms', $selectedKelas))->count();
        $absenHadir = Absen::where('tanggal', $selectedDate)
            ->where('status', 'Hadir')
            ->when($selectedKelas, fn($q) => $q->whereHas('siswa', fn($s) => $s->where('id_rooms', $selectedKelas)))
            ->count();
        $absenIzinSakit = Absen::where('tanggal', $selectedDate)
            ->whereIn('status', ['Izin', 'Sakit'])
            ->when($selectedKelas, fn($q) => $q->whereHas('siswa', fn($s) => $s->where('id_rooms', $selectedKelas)))
            ->count();
        $absenAlpa = max(0, $totalSiswa - ($absenHadir + $absenIzinSakit));
        $persentase = $totalSiswa > 0 ? round(($absenHadir / $totalSiswa) * 100, 1) : 0;

        $kpi = [
            'total' => $totalSiswa,
            'hadir' => $absenHadir,
            'izin_sakit' => $absenIzinSakit,
            'alpa' => $absenAlpa,
            'persentase' => $persentase,
        ];

        $kelasList = Kelas::all();

        return view('absensi.index', compact('siswaList', 'kpi', 'kelasList', 'selectedDate', 'selectedKelas'));
    }

    public function scanQr(Request $request)
    {
        $request->validate([
            'kode_barcode' => 'required|string',
            'id_guru' => 'nullable|exists:guru,id_guru',
        ]);

        $barcode = Barcode::where('kode_barcode', $request->kode_barcode)->first();
        if (!$barcode) {
            return back()->with('error', 'Barcode QR Siswa tidak terdaftar.');
        }

        $today = now()->toDateString();
        $now = now();
        $timeStr = $now->format('H:i');

        // Evaluasi Keterangan Waktu Masuk
        if ($timeStr < '07:05') {
            $ket = 'Datang Lebih Awal';
        } elseif ($timeStr <= '07:15') {
            $ket = 'Tepat Waktu';
        } elseif ($timeStr <= '07:30') {
            $ket = 'Sentuh Jam Toleransi';
        } else {
            $ket = 'Terlambat';
        }

        $idGuru = $request->id_guru ?? 1; // Default fallback guru piket/admin

        Absen::updateOrCreate(
            [
                'id_siswa' => $barcode->id_siswa,
                'tanggal' => $today,
            ],
            [
                'id_guru' => $idGuru,
                'id_barcode' => $barcode->id_barcode,
                'metode' => 'scan_qr',
                'status' => 'Hadir',
                'keterangan' => $ket,
                'waktu_absen' => $now,
            ]
        );

        return back()->with('success', "Presensi QR berhasil tercatat ({$ket}).");
    }

    public function updateManual(Request $request)
    {
        $request->validate([
            'id_siswa' => 'required|exists:siswa,id_siswa',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
            'berkas_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_guru' => 'nullable|exists:guru,id_guru',
        ]);

        $data = [
            'id_guru' => $request->id_guru ?? 1,
            'metode' => 'manual_guru',
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'waktu_absen' => now(),
        ];

        if ($request->hasFile('berkas_surat')) {
            $data['berkas_surat'] = $request->file('berkas_surat')->store('surat_izin', 'public');
        }

        Absen::updateOrCreate(
            [
                'id_siswa' => $request->id_siswa,
                'tanggal' => $request->tanggal,
            ],
            $data
        );

        return back()->with('success', 'Status absensi siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $absen = Absen::findOrFail($id);
        if ($absen->berkas_surat) {
            Storage::disk('public')->delete($absen->berkas_surat);
        }
        $absen->delete();

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }
}
