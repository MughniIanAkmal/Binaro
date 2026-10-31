<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Illuminate\Http\Request;

class AbsenScanController extends Controller
{
    public function __construct(private AbsensiService $absensiService) {}

    public function index()
    {
        $today = today()->toDateString();
        $hariIni = Absen::with('siswa.kelas')
            ->where(function ($q) use ($today) {
                $q->where('tanggal', $today)
                    ->orWhereDate('waktu_absen', $today);
            })
            ->orderByDesc('waktu_absen')
            ->get();

        $totalSiswa = Siswa::count();
        $tercatatCount = $hariIni->count();
        $hadirCount = $hariIni->where('status', 'Hadir')->count();
        $izinSakitCount = $hariIni->whereIn('status', ['Izin', 'Sakit'])->count();
        $alpaCount = $hariIni->where('status', 'Alpa')->count();
        $tanggalAbsen = today()->translatedFormat('d F Y');

        $daftarNama = Siswa::with('kelas')->orderBy('nm_siswa')->get()->map(fn ($s) => [
            'nama' => $s->nm_siswa,
            'nisn' => $s->nisn,
            'kelas' => $s->nama_kelas,
        ])->values();

        return view('guru.absen.scan', compact(
            'hariIni',
            'totalSiswa',
            'tercatatCount',
            'hadirCount',
            'izinSakitCount',
            'alpaCount',
            'daftarNama',
            'tanggalAbsen',
        ));
    }

    /**
     * Satu endpoint untuk dua jalur:
     *  - hasil scan kamera  (metode = scan)
     *  - kode diketik guru  (metode = manual, jika QR rusak)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50',
            'metode' => 'nullable|in:scan,manual',
        ]);

        $metode = ($data['metode'] ?? 'scan') === 'manual' ? 'manual_guru' : 'scan_qr';
        $hasil = $this->absensiService->catatScan($data['kode'], null, $metode);

        if ($hasil['hasil'] === 'tidak_dikenal') {
            return response()->json([
                'status' => 'tidak_dikenal',
                'message' => $hasil['message'],
            ], 404);
        }

        if ($hasil['hasil'] === 'duplikat') {
            return response()->json([
                'status' => 'duplikat',
                'success' => false,
                'message' => $hasil['message'],
            ], 200);
        }

        return response()->json([
            'status' => 'ok',
            'success' => true,
            'message' => $hasil['message'],
            'siswa' => ['nama' => $hasil['siswa']->nm_siswa, 'kelas' => $hasil['siswa']->nama_kelas],
            'jam' => $hasil['jam'],
            'statusAbsen' => $hasil['statusTampil'],
        ]);
    }

    /**
     * Input izin/sakit oleh guru berdasarkan nama siswa.
     * - Nama tidak terdaftar -> diminta isi ulang dengan benar.
     * - Jenis Sakit -> status Sakit. Jenis Izin (keterangan lain) -> wajib isi keterangan.
     * - Hasil tercatat dan muncul di rekap absensi.
     */
    public function storeIzin(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'jenis' => 'required|in:Sakit,Izin,Alpa',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($data['jenis'] === 'Izin' && trim($data['keterangan'] ?? '') === '') {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Anda memilih keterangan lain: wajib mengisi kolom keterangan.',
            ], 422);
        }

        $namaInput = trim($data['nama']);
        $siswa = null;

        // Format "Nama (NISN)" untuk nama kembar.
        if (preg_match('/\((\d+)\)\s*$/', $namaInput, $m)) {
            $siswa = Siswa::where('nisn', $m[1])->first();
        }

        if (! $siswa) {
            $cocok = Siswa::whereRaw('LOWER(nm_siswa) = ?', [mb_strtolower($namaInput)])->get();

            if ($cocok->isEmpty()) {
                $mirip = Siswa::where('nm_siswa', 'like', "%{$namaInput}%")->take(5)->get();
                $saran = $mirip->map(fn ($s) => $s->nm_siswa.' ('.$s->nisn.')')->join(', ');

                return response()->json([
                    'status' => 'tidak_dikenal',
                    'message' => 'Nama tidak terdaftar. Isi ulang nama dengan benar.'
                        .($saran ? ' Maksud Anda: '.$saran.'?' : ''),
                ], 404);
            }

            if ($cocok->count() > 1) {
                $daftar = $cocok->map(fn ($s) => $s->nm_siswa.' ('.$s->nisn.')')->join(', ');

                return response()->json([
                    'status' => 'ambigu',
                    'message' => 'Ada '.$cocok->count().' siswa bernama "'.$cocok->first()->nm_siswa
                        .'". Tulis "Nama (NISN)", contoh: '.$cocok->first()->nm_siswa.' ('.$cocok->first()->nisn
                        .'). Pilihan: '.$daftar,
                ], 422);
            }

            $siswa = $cocok->first();
        }

        $today = today()->toDateString();
        $status = $data['jenis']; // Sakit | Izin
        $keterangan = $status === 'Sakit' ? 'Sakit' : trim($data['keterangan']);
        $waktu = now();

        $existing = Absen::where('id_siswa', $siswa->id_siswa)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existing) {
            // Jika sebelumnya Alpha otomatis, izinkan koreksi jadi Izin/Sakit
            if ($existing->status === 'Alpa') {
                $existing->update([
                    'id_guru' => $this->absensiService->guruPencatatId(),
                    'metode' => 'manual_guru',
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'waktu_absen' => $waktu,
                ]);

                return response()->json([
                    'status' => 'ok',
                    'success' => true,
                    'message' => "{$status} tercatat: {$siswa->nm_siswa} (Alpa otomatis diperbarui)",
                    'siswa' => ['nama' => $siswa->nm_siswa, 'kelas' => $siswa->nama_kelas],
                    'jam' => $waktu->format('H:i'),
                    'statusAbsen' => $status,
                ]);
            }

            return response()->json([
                'status' => 'duplikat',
                'success' => false,
                'message' => "{$siswa->nm_siswa} sudah tercatat hari ini ({$existing->status}).",
            ], 200);
        }

        Absen::create([
            'id_guru' => $this->absensiService->guruPencatatId(),
            'id_siswa' => $siswa->id_siswa,
            'id_barcode' => null,
            'metode' => 'manual_guru',
            'status' => $status,
            'keterangan' => $keterangan,
            'tanggal' => $today,
            'waktu_absen' => $waktu,
        ]);

        return response()->json([
            'status' => 'ok',
            'success' => true,
            'message' => "{$status} tercatat: {$siswa->nm_siswa}",
            'siswa' => ['nama' => $siswa->nm_siswa, 'kelas' => $siswa->nama_kelas],
            'jam' => $waktu->format('H:i'),
            'statusAbsen' => $status,
        ]);
    }
}
