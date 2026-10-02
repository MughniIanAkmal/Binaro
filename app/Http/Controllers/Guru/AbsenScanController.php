<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\AbsensiSetting;
use App\Models\Barcode;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsenScanController extends Controller
{
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

        $daftarNama = Siswa::with('kelas')->orderBy('nm_siswa')->get()->map(fn ($s) => [
            'nama' => $s->nm_siswa,
            'nisn' => $s->nisn,
            'kelas' => $s->nama_kelas,
        ])->values();

        return view('guru.absen.scan', compact('hariIni', 'totalSiswa', 'tercatatCount', 'hadirCount', 'izinSakitCount', 'daftarNama'));
    }

    /**
     * Satu endpoint untuk dua jalur:
     *  - hasil scan kamera  (metode = scan)
     *  - kode diketik guru  (metode = manual, jika QR rusak)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode'   => 'required|string|max:50',
            'metode' => 'nullable|in:scan,manual',
        ]);

        $kode = strtoupper(trim($data['kode']));

        $barcode = Barcode::with('siswa.kelas')->where('kode_barcode', $kode)->first();

        // Toleran: hasil scan kadang membawa karakter tambahan (URL, newline, spasi).
        // Coba ekstrak kandidat kode (QR-XXXXXXXX atau BIN-NISN-XXXXXX) dari payload.
        if (! $barcode && preg_match('/\b((?:QR|BIN)-[A-Z0-9-]{4,40})\b/', $kode, $m)) {
            $barcode = Barcode::with('siswa.kelas')->where('kode_barcode', $m[1])->first();
        }

        if (! $barcode || ! $barcode->siswa) {
            return response()->json([
                'status'  => 'tidak_dikenal',
                'message' => 'Kode tidak dikenal. Pastikan kartu siswa masih berlaku.',
            ], 404);
        }

        $siswa = $barcode->siswa;
        $today = today()->toDateString();

        $guruId = $this->resolveGuruId($request);
        $metodeVal = ($data['metode'] ?? 'scan') === 'manual' ? 'manual_guru' : 'scan_qr';
        $waktu = now();
        $timeStr = $waktu->format('H:i');

        $batasAwal = class_exists(AbsensiSetting::class) ? AbsensiSetting::get('batas_awal', '07:00') : '07:00';
        $batasTepat = class_exists(AbsensiSetting::class) ? AbsensiSetting::get('batas_tepat', '08:00') : '08:00';
        $batasTutup = class_exists(AbsensiSetting::class) ? AbsensiSetting::get('batas_tutup', '12:00') : '12:00';

        if ($timeStr > $batasTutup) {
            // Melewati batas waktu admin: tetap tercatat, status Hadir dengan keterangan Terlambat.
            $keterangan = 'Terlambat';
        } elseif ($timeStr < $batasAwal) {
            $keterangan = 'Datang Lebih Awal';
        } elseif ($timeStr <= $batasTepat) {
            $keterangan = 'Tepat Waktu';
        } else {
            $keterangan = 'Terlambat';
        }

        $absen = Absen::where('id_siswa', $siswa->id_siswa)
            ->whereDate('tanggal', $today)
            ->first();

        if ($absen) {
            // Jika sebelumnya Alpha otomatis, tingkatkan jadi Hadir (scan susulan)
            if ($absen->status === 'Alpa') {
                $absen->update([
                    'id_guru' => $guruId,
                    'id_barcode' => $barcode->id_barcode,
                    'metode' => $metodeVal,
                    'status' => 'Hadir',
                    'keterangan' => $keterangan,
                    'waktu_absen' => $waktu,
                ]);

                return response()->json([
                    'status' => 'ok',
                    'success' => true,
                    'message' => "Hadir: {$siswa->nm_siswa} (Alpa otomatis diperbarui)",
                    'siswa' => ['nama' => $siswa->nm_siswa, 'kelas' => $siswa->nama_kelas],
                    'jam' => $waktu->format('H:i'),
                    'statusAbsen' => $keterangan === 'Terlambat' ? 'Terlambat' : 'Hadir',
                ]);
            }

            return response()->json([
                'status'  => 'duplikat',
                'success' => false,
                'message' => "{$siswa->nm_siswa} sudah absen hari ini.",
            ], 200);
        }

        $absenData = Absen::create([
            'id_guru'     => $guruId,
            'id_siswa'    => $siswa->id_siswa,
            'id_barcode'  => $barcode->id_barcode,
            'metode'      => $metodeVal,
            'status'      => 'Hadir',
            'keterangan'  => $keterangan,
            'tanggal'     => $today,
            'waktu_absen' => $waktu,
        ]);

        return response()->json([
            'status'  => 'ok',
            'success' => true,
            'message' => "Hadir: {$siswa->nm_siswa}",
            'siswa'   => ['nama' => $siswa->nm_siswa, 'kelas' => $siswa->nama_kelas],
            'jam'     => $waktu->format('H:i'),
            'statusAbsen' => $keterangan === 'Terlambat' ? 'Terlambat' : 'Hadir',
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
            'nama'       => 'required|string|max:100',
            'jenis'      => 'required|in:Sakit,Izin',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($data['jenis'] === 'Izin' && trim($data['keterangan'] ?? '') === '') {
            return response()->json([
                'status'  => 'invalid',
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
                    'status'  => 'tidak_dikenal',
                    'message' => 'Nama tidak terdaftar. Isi ulang nama dengan benar.'
                        .($saran ? ' Maksud Anda: '.$saran.'?' : ''),
                ], 404);
            }

            if ($cocok->count() > 1) {
                $daftar = $cocok->map(fn ($s) => $s->nm_siswa.' ('.$s->nisn.')')->join(', ');
                return response()->json([
                    'status'  => 'ambigu',
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
                    'id_guru' => $this->resolveGuruId($request),
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
                'status'  => 'duplikat',
                'success' => false,
                'message' => "{$siswa->nm_siswa} sudah tercatat hari ini ({$existing->status}).",
            ], 200);
        }

        Absen::create([
            'id_guru'     => $this->resolveGuruId($request),
            'id_siswa'    => $siswa->id_siswa,
            'id_barcode'  => null,
            'metode'      => 'manual_guru',
            'status'      => $status,
            'keterangan'  => $keterangan,
            'tanggal'     => $today,
            'waktu_absen' => $waktu,
        ]);

        return response()->json([
            'status'  => 'ok',
            'success' => true,
            'message' => "{$status} tercatat: {$siswa->nm_siswa}",
            'siswa'   => ['nama' => $siswa->nm_siswa, 'kelas' => $siswa->nama_kelas],
            'jam'     => $waktu->format('H:i'),
            'statusAbsen' => $status,
        ]);
    }

    private function resolveGuruId(Request $request)
    {
        $guruId = session('user_type') === 'guru' ? session('user_id') : ($request->user()?->id_guru ?? null);

        if (! $guruId || ! DB::table('guru')->where('id_guru', $guruId)->exists()) {
            $guruId = DB::table('guru')->value('id_guru');
        }

        if (! $guruId) {
            $guruId = DB::table('guru')->insertGetId([
                'nip' => 'GURU-DEMO',
                'nama_guru' => 'Guru Demo',
                'created_at' => now(),
            ], 'id_guru');
        }

        return $guruId;
    }
}
