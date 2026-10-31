<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\AbsensiSetting;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function __construct(private AbsensiService $absensiService) {}

    public function index(Request $request)
    {
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedKelas = $request->input('id_rooms');

        // Alpha otomatis untuk hari ini (lewat batas tutup) & hari lalu
        $this->absensiService->ensureAlphaOtomatis($selectedDate);

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
                'absen.berkas_surat',
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
        $totalSiswa = Siswa::when($selectedKelas, fn ($q) => $q->where('id_rooms', $selectedKelas))->count();
        $absenHadir = Absen::where('tanggal', $selectedDate)
            ->where('status', 'Hadir')
            ->when($selectedKelas, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('id_rooms', $selectedKelas)))
            ->count();
        $absenIzinSakit = Absen::where('tanggal', $selectedDate)
            ->whereIn('status', ['Izin', 'Sakit'])
            ->when($selectedKelas, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('id_rooms', $selectedKelas)))
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

        $hasil = $this->absensiService->catatScan($request->kode_barcode, $request->id_guru, 'scan_qr');

        if ($hasil['hasil'] === 'tidak_dikenal') {
            return back()->with('error', 'Barcode QR Siswa tidak terdaftar.');
        }

        if ($hasil['hasil'] === 'duplikat') {
            return back()->with('error', 'Siswa sudah melakukan absensi hari ini.');
        }

        $pesan = 'Presensi QR berhasil tercatat ('.$hasil['keterangan'].').';
        if ($hasil['upgrade']) {
            $pesan .= ' Status Alpa otomatis diperbarui.';
        }

        return back()->with('success', $pesan);
    }

    public function updateManual(Request $request)
    {
        if (! $request->filled('keterangan') && $request->filled('namaKeterangan')) {
            $request->merge(['keterangan' => $request->input('namaKeterangan')]);
        }

        $request->validate([
            'id_siswa' => 'required|exists:siswa,id_siswa',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
            'berkas_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_guru' => 'nullable|exists:guru,id_guru',
        ]);

        $data = [
            'id_guru' => $this->absensiService->guruPencatatId($request->id_guru),
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
        $guru = Guru::find(session('user_id'));
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedKelas = $request->input('id_rooms');
        $selectedStatus = $request->input('status');

        // Alpha otomatis untuk hari ini (lewat batas tutup) & hari lalu
        $this->absensiService->ensureAlphaOtomatis($selectedDate);

        $totalSiswa = Siswa::when($selectedKelas, fn ($q) => $q->where('id_rooms', $selectedKelas))->count();

        $absensToday = Absen::whereDate('tanggal', $selectedDate)
            ->when($selectedKelas, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('id_rooms', $selectedKelas)))
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

        $query = Siswa::with('kelas')
            ->join('absen', function ($join) use ($selectedDate) {
                $join->on('siswa.id_siswa', '=', 'absen.id_siswa')->whereDate('absen.tanggal', $selectedDate);
            })
            ->leftJoin('guru as pencatat', 'absen.id_guru', '=', 'pencatat.id_guru')
            ->leftJoin('kelas', 'siswa.id_rooms', '=', 'kelas.id_rooms')
            ->select([
                'siswa.id_siswa', 'siswa.nm_siswa', 'siswa.nisn', 'siswa.id_rooms',
                'kelas.pararel as nama_kelas', 'absen.id_absen', 'absen.metode',
                'absen.status', 'absen.keterangan', 'absen.waktu_absen', 'pencatat.nama_guru as nama_guru_pencatat',
            ]);

        if ($selectedKelas) {
            $query->where('siswa.id_rooms', $selectedKelas);
        }
        if ($selectedStatus) {
            $query->where('absen.status', $selectedStatus);
        }

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
            Storage::disk('public')->delete($absen->berkas_surat);
        }
        $absen->delete();

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }

    public function settingsView()
    {
        $batasAwal = AbsensiSetting::get('batas_awal', '07:00');
        $batasTepat = AbsensiSetting::get('batas_tepat', '08:00');
        $batasTutup = AbsensiSetting::get('batas_tutup', '12:00');

        return view('admin.absensi.settings', compact('batasAwal', 'batasTepat', 'batasTutup'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'batas_awal' => 'required|date_format:H:i',
            'batas_tepat' => 'required|date_format:H:i',
            'batas_tutup' => 'required|date_format:H:i',
        ], [
            'batas_awal.required' => 'Jam awal wajib diisi.',
            'batas_tepat.required' => 'Jam tepat waktu wajib diisi.',
            'batas_tutup.required' => 'Jam tutup sekolah wajib diisi.',
        ]);

        $awal = strtotime($request->batas_awal);
        $tepat = strtotime($request->batas_tepat);
        $tutup = strtotime($request->batas_tutup);

        if ($awal >= $tepat) {
            return back()->withInput()->withErrors(['batas_tepat' => 'Batas jam tepat waktu harus lebih lambat dari batas datang lebih awal.'])->with('error', 'Batas jam tepat waktu harus lebih lambat dari batas datang lebih awal.');
        }

        if ($tepat >= $tutup) {
            return back()->withInput()->withErrors(['batas_tutup' => 'Jam tutup sekolah harus lebih lambat dari batas jam tepat waktu.'])->with('error', 'Jam tutup sekolah harus lebih lambat dari batas jam tepat waktu.');
        }

        AbsensiSetting::set('batas_awal', $request->batas_awal);
        AbsensiSetting::set('batas_tepat', $request->batas_tepat);
        AbsensiSetting::set('batas_tutup', $request->batas_tutup);

        return redirect()->route('admin.absensi.settings')->with('success', 'Pengaturan jam absensi & jam operasional sekolah berhasil disimpan.');
    }
}
