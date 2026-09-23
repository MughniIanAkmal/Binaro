<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absen;
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

        $siswas = Siswa::with('barcode', 'kelas')
            ->orderBy('id_rooms')
            ->orderBy('nm_siswa')
            ->get();

        return view('guru.absen.scan', compact('hariIni', 'siswas'));
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

        if (! $barcode || ! $barcode->siswa) {
            return response()->json([
                'status'  => 'tidak_dikenal',
                'message' => 'Kode tidak dikenal. Pastikan kartu siswa masih berlaku.',
            ], 404);
        }

        $siswa = $barcode->siswa;
        $today = today()->toDateString();

        $absen = Absen::where('id_siswa', $siswa->id_siswa)
            ->where('tanggal', $today)
            ->first();

        if ($absen) {
            return response()->json([
                'status'  => 'duplikat',
                'message' => "{$siswa->nama_siswa} sudah absen hari ini.",
            ], 200);
        }

        $guruId = session('user_type') === 'guru' ? session('user_id') : null;
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

        $metodeVal = ($data['metode'] ?? 'scan') === 'manual' ? 'manual_guru' : 'scan_qr';
        $waktu = now();
        $timeStr = $waktu->format('H:i');

        $batasAwal = \App\Models\AbsensiSetting::get('batas_awal', '07:00');
        $batasTepat = \App\Models\AbsensiSetting::get('batas_tepat', '08:00');
        $batasTutup = \App\Models\AbsensiSetting::get('batas_tutup', '12:00');

        if ($timeStr > $batasTutup) {
            return response()->json(['status' => 'err', 'message' => 'Sekolah sudah tutup.'], 400);
        }

        $absen = Absen::where('id_siswa', $siswa->id_siswa)
            ->where('tanggal', $today)
            ->first();

        if ($absen) {
            return response()->json(['status' => 'duplikat', 'message' => "{$siswa->nama_siswa} sudah absen hari ini."], 200);
        }

        if ($timeStr < $batasAwal) {
            $keterangan = 'Datang Lebih Awal';
        } elseif ($timeStr <= $batasTepat) {
            $keterangan = 'Tepat Waktu';
        } else {
            $keterangan = 'Terlambat';
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
            'message' => "Hadir: {$siswa->nama_siswa}",
            'siswa'   => ['nama' => $siswa->nama_siswa, 'kelas' => $siswa->nama_kelas],
            'jam'     => $waktu->format('H:i'),
        ]);
    }
}
