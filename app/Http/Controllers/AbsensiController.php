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

        $batasAwal = \App\Models\AbsensiSetting::get('batas_awal', '07:00');
        $batasTepat = \App\Models\AbsensiSetting::get('batas_tepat', '08:00');
        $batasTutup = \App\Models\AbsensiSetting::get('batas_tutup', '12:00');

        if ($timeStr > $batasTutup) {
            // Melewati batas waktu admin: tetap tercatat sebagai Terlambat.
            $ket = 'Terlambat';
        } elseif ($timeStr < $batasAwal) {
            $ket = 'Datang Lebih Awal';
        } elseif ($timeStr <= $batasTepat) {
            $ket = 'Tepat Waktu';
        } else {
            $ket = 'Terlambat';
        }

        $existing = Absen::where('id_siswa', $barcode->id_siswa)
            ->where('tanggal', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'Siswa sudah melakukan absensi hari ini.');
        }

        $idGuru = $request->id_guru ?? 1; // Default fallback guru piket/admin

        Absen::create(
            [
                'id_siswa' => $barcode->id_siswa,
                'tanggal' => $today,
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

    public function rekap(Request $request)
    {
        $guru = \App\Models\Guru::find(session('user_id'));
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedKelas = $request->input('id_rooms');
        $selectedStatus = $request->input('status');

        $totalSiswa = Siswa::when($selectedKelas, fn($q) => $q->where('id_rooms', $selectedKelas))->count();

        $absensToday = Absen::where('tanggal', $selectedDate)
            ->when($selectedKelas, fn($q) => $q->whereHas('siswa', fn($s) => $s->where('id_rooms', $selectedKelas)))
            ->get();

        $hadir = $absensToday->where('status', 'Hadir')->count();
        $izin = $absensToday->where('status', 'Izin')->count();
        $sakit = $absensToday->where('status', 'Sakit')->count();
        $alpa = $absensToday->where('status', 'Alpa')->count();
        $persentase = $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100, 1) : 0;

        $summary = [
            'total_siswa' => $totalSiswa,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
            'persentase' => $persentase,
        ];

        $query = Siswa::with(['kelas', 'absens'])
            ->leftJoin('absen', function ($join) use ($selectedDate) {
                $join->on('siswa.id_siswa', '=', 'absen.id_siswa')->where('absen.tanggal', $selectedDate);
            })
            ->leftJoin('guru as pencatat', 'absen.id_guru', '=', 'pencatat.id_guru')
            ->leftJoin('kelas', 'siswa.id_rooms', '=', 'kelas.id_rooms')
            ->select([
                'siswa.id_siswa', 'siswa.nm_siswa', 'siswa.nisn', 'siswa.id_rooms',
                'kelas.pararel as nama_kelas', 'absen.id_absen', 'absen.metode',
                'absen.status', 'absen.waktu_absen', 'pencatat.nama_guru as nama_guru_pencatat',
            ]);

        if ($selectedKelas) $query->where('siswa.id_rooms', $selectedKelas);
        if ($selectedStatus) $query->where('absen.status', $selectedStatus);

        $siswaList = $query->orderBy('siswa.nm_siswa')->paginate(20)->withQueryString();

        $alpaAlertSiswaIds = Absen::where('status', 'Alpa')
            ->where('tanggal', '>=', now()->subDays(7)->toDateString())
            ->selectRaw('id_siswa, count(*) as total')
            ->groupBy('id_siswa')->having('total', '>', 2)->pluck('id_siswa')->toArray();

        $kelasList = Kelas::all();

        return view('admin.absensi.rekap', compact('summary', 'siswaList', 'kelasList', 'selectedDate', 'selectedKelas', 'selectedStatus', 'alpaAlertSiswaIds', 'guru'));
    }

    public function destroy($id)
    {
        $absen = Absen::findOrFail($id);
        if ($absen->berkas_surat) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($absen->berkas_surat);
        }
        $absen->delete();

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'batas_awal' => 'required|date_format:H:i',
            'batas_tepat' => 'required|date_format:H:i',
            'batas_tutup' => 'required|date_format:H:i',
        ]);

        \App\Models\AbsensiSetting::set('batas_awal', $request->batas_awal);
        \App\Models\AbsensiSetting::set('batas_tepat', $request->batas_tepat);
        \App\Models\AbsensiSetting::set('batas_tutup', $request->batas_tutup);

        return back()->with('success', 'Pengaturan waktu berhasil disimpan.');
    }
}
