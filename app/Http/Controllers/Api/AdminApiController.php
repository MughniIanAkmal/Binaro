<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Barcode;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminApiController extends Controller
{
    // GET /api/admin/absensi/rekap
    public function rekapAbsensi(Request $request)
    {
        $fecha = $request->query('fecha') ?? $request->query('tanggal') ?? now()->toDateString();
        $idRooms = $request->query('id_rooms');
        $status = $request->query('status');

        // Alpha otomatis untuk hari ini (lewat batas tutup) & hari lalu
        \App\Http\Controllers\AbsensiController::ensureAlphaOtomatis($fecha);

        $query = Absen::query()
            ->with(['siswa.kelas', 'guru'])
            ->whereDate('tanggal', $fecha);

        if ($idRooms) {
            $query->whereHas('siswa', function ($q) use ($idRooms) {
                $q->where('id_rooms', $idRooms);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $summaryQuery = Siswa::query();
        if ($idRooms) {
            $summaryQuery->where('id_rooms', $idRooms);
        }
        $totalSiswa = $summaryQuery->count();

        $absensToday = Absen::query()
            ->whereDate('tanggal', $fecha)
            ->when($idRooms, function ($q) use ($idRooms) {
                $q->whereHas('siswa', function ($q2) use ($idRooms) {
                    $q2->where('id_rooms', $idRooms);
                });
            })
            ->get();

        $hadir = $absensToday->where('status', 'Hadir')->count();
        $izin = $absensToday->where('status', 'Izin')->count();
        $sakit = $absensToday->where('status', 'Sakit')->count();
        $alpa = $absensToday->where('status', 'Alpa')->count();

        $summary = [
            'total_siswa' => $totalSiswa,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
        ];

        $data = $query->paginate(20);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $data,
        ]);
    }

    // GET /api/admin/qr/regenerate/{siswa_id}
    public function regenerateQr(Request $request, $siswa_id)
    {
        $siswa = Siswa::find($siswa_id);
        if (! $siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan',
            ], 404);
        }

        $barcode = Barcode::where('id_siswa', $siswa_id)->first();

        if ($barcode && $barcode->updated_at && $barcode->updated_at->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Batas maksimum regenerate barcode 1x per hari.',
            ], 429);
        }

        $newKode = 'BC-' . strtoupper(Str::random(8));

        if (! $barcode) {
            $barcode = Barcode::create([
                'id_siswa' => $siswa_id,
                'kode_barcode' => $newKode,
                'is_active' => true,
            ]);
        } else {
            $barcode->update([
                'kode_barcode' => $newKode,
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil diperbarui',
            'data' => $barcode,
        ]);
    }
}