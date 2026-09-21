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
        $hariIni = Absen::with('siswa.kelas')
            ->whereDate('waktu_absen', today())
            ->orderByDesc('waktu_absen')
            ->get();

        $siswas = Siswa::with('barcode', 'kelas')
            ->orderBy('id_kelas')
            ->orderBy('nama')
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

        $absen = Absen::where('id_siswa', $siswa->id_siswa)
            ->whereDate('waktu_absen', today())
            ->first();

        $baru = false;
        if (! $absen) {
            $guruId = $request->user()?->id_guru;
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

            $absen = Absen::create([
                'id_guru'    => $guruId,
                'id_siswa'   => $siswa->id_siswa,
                'id_barcode' => $barcode->id_barcode,
                'status'     => 'Hadir',
                'waktu_absen' => now(),
            ]);
            $baru = true;
        }

        return response()->json([
            'status'  => $baru ? 'ok' : 'duplikat',
            'message' => $baru
                ? "Hadir: {$siswa->nama_siswa}"
                : "{$siswa->nama_siswa} sudah absen hari ini.",
            'siswa'   => ['nama' => $siswa->nama_siswa, 'kelas' => $siswa->nama_kelas],
            'jam'     => $absen->waktu_absen?->format('H:i'),
        ]);
    }
}
