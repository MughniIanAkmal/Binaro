<?php

namespace App\Features\Controllers\Admin;

use App\Features\Controllers\Controller;
use App\Features\Models\Absen;
use App\Features\Models\Barcode;
use App\Features\Models\Kelas;
use App\Features\Models\Siswa;
use App\Features\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Revisi 5 — QR & Absensi digabung dalam satu halaman admin.
 *
 * Halaman ini TIDAK menduplikasi logika: pembuatan QR tetap lewat
 * BarcodeController, scan/manual absen tetap lewat AbsensiController.
 * Controller ini hanya menggabungkan tampilannya: satu daftar siswa
 * yang memuat QR-nya sekaligus status absen hari itu, plus KPI keduanya.
 * Route lama (/admin/qr-siswa dan /absensi) tetap jalan.
 */
class QrAbsensiController extends Controller
{
    public function __construct(private AbsensiService $absensiService) {}

    public function index(Request $request)
    {
        $selectedDate = $request->input('tanggal', now()->toDateString());
        $selectedKelas = $request->input('id_rooms');

        $this->absensiService->ensureAlphaOtomatis($selectedDate);

        $query = Siswa::query()
            ->with('barcode')
            ->leftJoin('absen', function ($join) use ($selectedDate) {
                $join->on('siswa.id_siswa', '=', 'absen.id_siswa')
                    ->where('absen.tanggal', '=', $selectedDate);
            })
            ->leftJoin('kelas', 'siswa.id_rooms', '=', 'kelas.id_rooms')
            ->select([
                'siswa.*',
                'kelas.pararel as nama_kelas',
                'absen.id_absen',
                'absen.metode',
                DB::raw("COALESCE(absen.status, 'Alpa') as status_kehadiran"),
                'absen.waktu_absen',
                'absen.keterangan',
            ]);

        if ($selectedKelas) {
            $query->where('siswa.id_rooms', $selectedKelas);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('siswa.nm_siswa', 'like', "%{$q}%")
                    ->orWhere('siswa.nisn', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'izin_sakit') {
                $query->whereIn(DB::raw("COALESCE(absen.status, 'Alpa')"), ['Izin', 'Sakit']);
            } elseif ($request->status === 'belum_qr') {
                $query->whereDoesntHave('barcode');
            } else {
                $query->where(DB::raw("COALESCE(absen.status, 'Alpa')"), $request->status);
            }
        }

        $siswaList = $query->orderBy('siswa.nm_siswa')->paginate(15)->withQueryString();

        $totalSiswa = Siswa::when($selectedKelas, fn ($q) => $q->where('id_rooms', $selectedKelas))->count();
        $absenHadir = Absen::where('tanggal', $selectedDate)->where('status', 'Hadir')
            ->when($selectedKelas, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('id_rooms', $selectedKelas)))->count();
        $absenIzinSakit = Absen::where('tanggal', $selectedDate)->whereIn('status', ['Izin', 'Sakit'])
            ->when($selectedKelas, fn ($q) => $q->whereHas('siswa', fn ($s) => $s->where('id_rooms', $selectedKelas)))->count();
        $absenAlpa = max(0, $totalSiswa - ($absenHadir + $absenIzinSakit));

        $kpi = [
            'total' => $totalSiswa,
            'hadir' => $absenHadir,
            'izin_sakit' => $absenIzinSakit,
            'alpa' => $absenAlpa,
            'persentase' => $totalSiswa > 0 ? round(($absenHadir / $totalSiswa) * 100, 1) : 0,
            'sudah_qr' => Barcode::count(),
        ];

        $kelasList = Kelas::all();

        return view('admin.qr_absensi.index', compact('siswaList', 'kpi', 'kelasList', 'selectedDate', 'selectedKelas'));
    }
}
