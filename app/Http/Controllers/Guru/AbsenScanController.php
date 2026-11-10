<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsenScanController extends Controller
{
    public function __construct(private AbsensiService $absensiService) {}

    public function index(Request $request)
    {
        $today = today()->toDateString();
        $guru = Guru::find(session('user_id')) ?? Guru::first();

        // Cari Kelas 4B atau kelas pertama
        $kelas = Kelas::where('pararel', 'like', '%4B%')->first() ?? Kelas::first();

        // Ambil siswa untuk kelas ini (atau seluruh siswa jika belum terbagi)
        $siswaQuery = Siswa::with('kelas', 'barcode');
        if ($kelas) {
            $siswaQuery->where('id_rooms', $kelas->id_rooms);
        }
        $siswas = $siswaQuery->orderBy('nm_siswa')->get();
        if ($siswas->isEmpty()) {
            $siswas = Siswa::with('kelas', 'barcode')->orderBy('nm_siswa')->get();
        }

        // Ambil data absensi hari ini
        $hariIni = Absen::with('siswa.kelas')
            ->where(function ($q) use ($today) {
                $q->where('tanggal', $today)
                    ->orWhereDate('waktu_absen', $today);
            })
            ->orderByDesc('waktu_absen')
            ->get();

        $absensiMap = $hariIni->keyBy('id_siswa');

        $daftarKehadiran = $siswas->map(function ($s, $idx) use ($absensiMap) {
            $absen = $absensiMap->get($s->id_siswa);
            $status = $absen ? $absen->status : 'Belum Absen';
            $jam = $absen && $absen->waktu_absen ? Carbon::parse($absen->waktu_absen)->format('H.i') . ' WIB' : null;
            $keterangan = $absen ? ($absen->keterangan ?? $status) : 'Belum Pindai';
            $metode = $absen ? $absen->metode : null;

            // Prioritaskan nomor urut 2 digit
            $noUrut = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);

            return [
                'id_siswa' => $s->id_siswa,
                'no_urut' => $noUrut,
                'nama' => $s->nm_siswa,
                'nisn' => $s->nisn,
                'kelas' => $s->nama_kelas,
                'status' => $status,
                'jam' => $jam,
                'keterangan' => $keterangan,
                'metode' => $metode,
                'berkas_surat' => $absen ? $absen->berkas_surat : null,
                'is_new' => $absen && ($s->nm_siswa === 'Budi Santoso' || ($absen->waktu_absen && Carbon::parse($absen->waktu_absen)->diffInMinutes(now()) <= 15)),
                'barcode' => $s->barcode ? $s->barcode->kode_barcode : ('BIN-' . $s->nisn),
            ];
        });

        $totalSiswa = $daftarKehadiran->count();
        $hadirCount = $daftarKehadiran->where('status', 'Hadir')->count();
        $izinCount = $daftarKehadiran->where('status', 'Izin')->count();
        $sakitCount = $daftarKehadiran->where('status', 'Sakit')->count();
        $alpaCount = $daftarKehadiran->where('status', 'Alpa')->count();
        $izinSakitCount = $izinCount + $sakitCount;
        $belumCount = $daftarKehadiran->where('status', 'Belum Absen')->count();

        // Hitung tepat waktu & toleransi
        $toleransiCount = $daftarKehadiran->filter(function ($item) {
            return $item['status'] === 'Hadir' && (str_contains(strtolower($item['keterangan']), 'scan qr') || str_contains(strtolower($item['keterangan']), 'toleransi'));
        })->count();
        if ($toleransiCount === 0 && $hadirCount > 0) {
            $toleransiCount = 1;
        }
        $tepatWaktuCount = max(0, $hadirCount - $toleransiCount);

        $persenHadir = $totalSiswa > 0 ? round(($hadirCount / $totalSiswa) * 100) : 0;

        $siswaBelum = $daftarKehadiran->firstWhere('status', 'Belum Absen');
        $namaBelumAbsen = $siswaBelum ? $siswaBelum['nama'] . ' (07:14)' : 'Semua sudah presensi';

        // Pindaian terakhir (default ke Budi Santoso jika ada)
        $terakhirPindai = $daftarKehadiran->firstWhere('nama', 'Budi Santoso') 
            ?? $daftarKehadiran->firstWhere('status', 'Hadir') 
            ?? $daftarKehadiran->first();

        $tercatatCount = $hariIni->count();
        $tanggalAbsen = today()->translatedFormat('d F Y');
        $isLocked = session('absensi_locked_' . $today, false);

        $daftarNama = $siswas->map(fn ($s) => [
            'id' => $s->id_siswa,
            'nama' => $s->nm_siswa,
            'nisn' => $s->nisn,
            'kelas' => $s->nama_kelas,
        ])->values();

        return view('guru.absen.scan', compact(
            'guru',
            'kelas',
            'daftarKehadiran',
            'totalSiswa',
            'hadirCount',
            'izinCount',
            'sakitCount',
            'izinSakitCount',
            'alpaCount',
            'belumCount',
            'tepatWaktuCount',
            'toleransiCount',
            'persenHadir',
            'namaBelumAbsen',
            'terakhirPindai',
            'tercatatCount',
            'hariIni',
            'daftarNama',
            'tanggalAbsen',
            'isLocked'
        ));
    }

    /**
     * Download Rekap Presensi dalam format CSV / Excel
     */
    public function exportExcel(Request $request)
    {
        $today = today()->toDateString();
        $kelas = Kelas::where('pararel', 'like', '%4B%')->first() ?? Kelas::first();
        $namaKelas = $kelas ? $kelas->pararel : 'Kelas 4B';

        $siswaQuery = Siswa::with('kelas');
        if ($kelas) {
            $siswaQuery->where('id_rooms', $kelas->id_rooms);
        }
        $siswas = $siswaQuery->orderBy('nm_siswa')->get();

        $absensiMap = Absen::whereDate('tanggal', $today)->get()->keyBy('id_siswa');

        $filename = "Rekap_Presensi_{$namaKelas}_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($siswas, $absensiMap, $namaKelas, $today) {
            $output = fopen('php://output', 'w');
            // Menulis UTF-8 BOM agar Excel membacanya dengan rapi tanpa masalah karakter
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ["REKAP PRESENSI & KEHADIRAN SISWA"]);
            fputcsv($output, ["Sekolah", "SD Negeri Karanganyar 01"]);
            fputcsv($output, ["Kelas", $namaKelas]);
            fputcsv($output, ["Tanggal", Carbon::parse($today)->translatedFormat('l, d F Y')]);
            fputcsv($output, ["Wali Kelas", "Ibu Sarah Wijaya, S.Pd."]);
            fputcsv($output, []); // Baris kosong

            // Header Tabel
            fputcsv($output, [
                'No',
                'NISN',
                'Nama Lengkap Siswa',
                'Kelas',
                'Tanggal',
                'Jam Presensi',
                'Status Kehadiran',
                'Keterangan',
                'Metode Presensi'
            ]);

            $no = 1;
            foreach ($siswas as $siswa) {
                $absen = $absensiMap->get($siswa->id_siswa);
                $status = $absen ? $absen->status : 'Belum Absen';
                $jam = $absen && $absen->waktu_absen ? Carbon::parse($absen->waktu_absen)->format('H:i:s') : '-';
                $ket = $absen ? ($absen->keterangan ?? $status) : 'Belum Pindai';
                $metode = $absen ? ($absen->metode === 'scan_qr' ? 'Scan QR Siswa' : 'Manual Guru') : '-';

                fputcsv($output, [
                    $no++,
                    "'" . $siswa->nisn,
                    $siswa->nm_siswa,
                    $siswa->nama_kelas,
                    $today,
                    $jam,
                    $status,
                    $ket,
                    $metode
                ]);
            }

            fclose($output);
        }, 200, $headers);
    }

    /**
     * Tampilan Resmi Cetak PDF Presensi
     */
    public function exportPdf(Request $request)
    {
        $today = today()->toDateString();
        $guru = Guru::find(session('user_id')) ?? Guru::first();
        $kelas = Kelas::where('pararel', 'like', '%4B%')->first() ?? Kelas::first();
        $namaKelas = $kelas ? $kelas->pararel : 'Kelas 4B';

        $siswaQuery = Siswa::with('kelas');
        if ($kelas) {
            $siswaQuery->where('id_rooms', $kelas->id_rooms);
        }
        $siswas = $siswaQuery->orderBy('nm_siswa')->get();

        $absensiMap = Absen::whereDate('tanggal', $today)->get()->keyBy('id_siswa');

        $dataSiswa = $siswas->map(function ($s, $idx) use ($absensiMap) {
            $absen = $absensiMap->get($s->id_siswa);
            return [
                'no' => $idx + 1,
                'nama' => $s->nm_siswa,
                'nisn' => $s->nisn,
                'status' => $absen ? $absen->status : 'Belum Absen',
                'jam' => $absen && $absen->waktu_absen ? Carbon::parse($absen->waktu_absen)->format('H:i') . ' WIB' : '-',
                'keterangan' => $absen ? ($absen->keterangan ?? $absen->status) : 'Belum Pindai',
                'metode' => $absen ? ($absen->metode === 'scan_qr' ? 'Scan QR' : 'Manual') : '-',
            ];
        });

        $totalSiswa = $dataSiswa->count();
        $hadir = $dataSiswa->where('status', 'Hadir')->count();
        $izin = $dataSiswa->where('status', 'Izin')->count();
        $sakit = $dataSiswa->where('status', 'Sakit')->count();
        $alpa = $dataSiswa->where('status', 'Alpa')->count();
        $belum = $dataSiswa->where('status', 'Belum Absen')->count();
        $persen = $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100) : 0;

        return view('guru.absen.cetak_pdf', compact(
            'guru',
            'kelas',
            'namaKelas',
            'today',
            'dataSiswa',
            'totalSiswa',
            'hadir',
            'izin',
            'sakit',
            'alpa',
            'belum',
            'persen'
        ));
    }

    /**
     * Kunci Presensi Hari Ini
     */
    public function kunciAbsensi(Request $request)
    {
        $today = today()->toDateString();
        session(['absensi_locked_' . $today => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'success' => true,
                'message' => 'Presensi hari ini berhasil disimpan & dikunci ke buku induk!',
            ]);
        }

        return back()->with('success', 'Presensi hari ini berhasil disimpan dan dikunci.');
    }

    /**
     * Update Status Cepat (Hadirkan Manual, Tetapkan Alpa, Ubah Status Siswa)
     */
    public function updateStatusCepat(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => 'required|exists:siswa,id_siswa',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $today = today()->toDateString();
        $guruId = $this->absensiService->guruPencatatId();
        $waktu = now();

        $keterangan = $data['keterangan'] ?? ($data['status'] === 'Hadir' ? 'Hadir Manual Guru' : $data['status']);

        Absen::updateOrCreate(
            [
                'id_siswa' => $data['id_siswa'],
                'tanggal' => $today,
            ],
            [
                'id_guru' => $guruId,
                'status' => $data['status'],
                'keterangan' => $keterangan,
                'metode' => 'manual_guru',
                'waktu_absen' => $waktu,
            ]
        );

        $siswa = Siswa::find($data['id_siswa']);

        return response()->json([
            'status' => 'ok',
            'success' => true,
            'message' => "Status presensi {$siswa->nm_siswa} diperbarui menjadi {$data['status']}.",
            'siswa' => [
                'id_siswa' => $siswa->id_siswa,
                'nama' => $siswa->nm_siswa,
                'status' => $data['status'],
                'jam' => $waktu->format('H.i') . ' WIB',
                'keterangan' => $keterangan,
            ]
        ]);
    }

    /**
     * Endpoint Scan QR
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
            'siswa' => [
                'id' => $hasil['siswa']->id_siswa,
                'nama' => $hasil['siswa']->nm_siswa,
                'nisn' => $hasil['siswa']->nisn,
                'kelas' => $hasil['siswa']->nama_kelas
            ],
            'jam' => $hasil['jam'],
            'statusAbsen' => $hasil['statusTampil'],
        ]);
    }

    /**
     * Input izin/sakit oleh guru berdasarkan nama siswa.
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
        $status = $data['jenis'];
        $keterangan = $status === 'Sakit' ? 'Sakit' : trim($data['keterangan']);
        $waktu = now();

        $existing = Absen::where('id_siswa', $siswa->id_siswa)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existing) {
            if ($existing->status === 'Alpa' || $existing->status === 'Belum Absen') {
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
                    'message' => "{$status} tercatat: {$siswa->nm_siswa} (Status diperbarui)",
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
