<?php

namespace App\Features\Controllers;

use App\Features\Models\Absen;
use App\Features\Models\AbsensiSetting;
use App\Features\Models\Guru;
use App\Features\Models\Kelas;
use App\Features\Models\Siswa;
use App\Features\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

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

        // Cek otorisasi via policy: hanya admin yang bisa menghapus.
        if (! Gate::denies('delete', $absen)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus record absensi ini.');
        }

        if ($absen->berkas_surat) {
            Storage::disk('public')->delete($absen->berkas_surat);
        }
        $absen->delete();

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Ekspor rekap absensi ke format CSV (bisa dibuka di Excel).
     */
    public function exportExcel(Request $request)
    {
        $guru = Guru::find(session('user_id'));
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedKelas = $request->input('id_rooms');
        $selectedStatus = $request->input('status');

        $this->absensiService->ensureAlphaOtomatis($selectedDate);

        $query = Siswa::with('kelas')
            ->join('absen', function ($join) use ($selectedDate) {
                $join->on('siswa.id_siswa', '=', 'absen.id_siswa')->whereDate('absen.tanggal', $selectedDate);
            })
            ->leftJoin('guru as pencatat', 'absen.id_guru', '=', 'pencatat.id_guru')
            ->leftJoin('kelas', 'siswa.id_rooms', '=', 'kelas.id_rooms')
            ->select([
                'siswa.id_siswa', 'siswa.nm_siswa', 'siswa.nisn', 'siswa.id_rooms',
                'kelas.pararel as nama_kelas', 'absen.metode',
                'absen.status', 'absen.keterangan', 'absen.waktu_absen',
                'pencatat.nama_guru as nama_guru_pencatat',
            ]);

        if ($selectedKelas) {
            $query->where('siswa.id_rooms', $selectedKelas);
        }
        if ($selectedStatus) {
            $query->where('absen.status', $selectedStatus);
        }

        $rows = $query->orderBy('siswa.nm_siswa')->get();

        $namaKelas = Kelas::find($selectedKelas)?->pararel ?? 'Semua Kelas';
        $filename = "Rekap_Absensi_{$namaKelas}_" . str_replace('-', '', $selectedDate) . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($rows, $selectedDate) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['REKAPITULASI ABSENSI SISWA']);
            fputcsv($output, ['Tanggal', Carbon::parse($selectedDate)->translatedFormat('l, d F Y')]);
            fputcsv($output, []);

            fputcsv($output, [
                'No', 'NISN', 'Nama Siswa', 'Kelas', 'Status',
                'Jam Masuk', 'Guru Pencatat', 'Metode', 'Keterangan',
            ]);

            $no = 1;
            foreach ($rows as $row) {
                fputcsv($output, [
                    $no++,
                    "'" . $row->nisn,
                    $row->nm_siswa,
                    $row->nama_kelas ?? '-',
                    $row->status,
                    $row->waktu_absen ? Carbon::parse($row->waktu_absen)->format('H:i:s') : '-',
                    $row->nama_guru_pencatat ?? '-',
                    $row->metode === 'scan_qr' ? 'Scan QR' : 'Manual',
                    $row->keterangan ?? '-',
                ]);
            }

            fclose($output);
        }, 200, $headers);
    }

    /**
     * Cetak rekap absensi ke halaman HTML yang siap dicetak sebagai PDF.
     * Menggunakan view dedicated untuk print dengan styling minimal.
     */
    public function exportPdf(Request $request)
    {
        $guru = Guru::find(session('user_id'));
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedKelas = $request->input('id_rooms');
        $selectedStatus = $request->input('status');

        $this->absensiService->ensureAlphaOtomatis($selectedDate);

        $totalSiswa = Siswa::when($selectedKelas, fn ($q) => $q->where('id_rooms', $selectedKelas))->count();
        $absensToday = Absen::whereDate('tanggal', $selectedDate)
            ->when($selectedKelas, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('id_rooms', $selectedKelas)))
            ->get();

        $hadir = $absensToday->where('status', 'Hadir')->count();
        $izin = $absensToday->where('status', 'Izin')->count();
        $sakit = $absensToday->where('status', 'Sakit')->count();
        $alpa = $absensToday->where('status', 'Alpa')->count();

        $query = Siswa::with('kelas')
            ->join('absen', function ($join) use ($selectedDate) {
                $join->on('siswa.id_siswa', '=', 'absen.id_siswa')->whereDate('absen.tanggal', $selectedDate);
            })
            ->leftJoin('guru as pencatat', 'absen.id_guru', '=', 'pencatat.id_guru')
            ->leftJoin('kelas', 'siswa.id_rooms', '=', 'kelas.id_rooms')
            ->select([
                'siswa.id_siswa', 'siswa.nm_siswa', 'siswa.nisn', 'siswa.id_rooms',
                'kelas.pararel as nama_kelas', 'absen.metode',
                'absen.status', 'absen.keterangan', 'absen.waktu_absen',
                'pencatat.nama_guru as nama_guru_pencatat',
            ]);

        if ($selectedKelas) {
            $query->where('siswa.id_rooms', $selectedKelas);
        }
        if ($selectedStatus) {
            $query->where('absen.status', $selectedStatus);
        }

        $rows = $query->orderBy('siswa.nm_siswa')->get();

        return view('admin.absensi.rekap_pdf', compact(
            'rows', 'selectedDate', 'selectedKelas', 'selectedStatus',
            'totalSiswa', 'hadir', 'izin', 'sakit', 'alpa', 'guru'
        ));
    }

    /**
     * Halaman pengaturan jam absen lama. Sekarang pengaturan berupa pop-up di
     * halaman Presensi Siswa / QR & Absensi, jadi URL lama dialihkan ke sana
     * dengan penanda agar modalnya langsung terbuka.
     */
    public function settingsView()
    {
        return redirect()
            ->route('admin.qr_absensi.index', ['open_settings' => 1])
            ->with('info', 'Pengaturan jam absen sekarang tersedia sebagai pop-up di halaman QR & Absensi.');
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

        // Kembali ke halaman tempat modal dibuka agar admin tetap di konteks tabel.
        return back()->with('success', 'Pengaturan jam absensi & jam operasional sekolah berhasil disimpan.');
    }
}
