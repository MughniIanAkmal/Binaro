<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Absen;
use App\Features\Models\Siswa;
use App\Features\Services\AbsensiService;
use Illuminate\Http\Request;

class AbsensiController extends ApiController
{
    public function __construct(private AbsensiService $absensiService) {}

    public function todayAttendance(Request $request)
    {
        $userId = $this->resolveSiswaId($request);
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $today = now()->toDateString();
        $absen = Absen::where('id_siswa', $userId)
            ->where('tanggal', $today)
            ->first();

        return $this->success([
            'id_siswa' => $userId,
            'tanggal' => $today,
            'status' => $absen ? $absen->status : 'Belum Hadir',
            'keterangan' => $absen ? ($absen->keterangan ?? 'Tepat Waktu') : 'Belum melakukan absensi hari ini',
            'waktu_absen' => $absen?->waktu_absen,
            'metode' => $absen?->metode,
            'detail' => $absen,
        ], 'Data absensi hari ini berhasil dimuat');
    }

    public function selfScan(Request $request)
    {
        $userId = $this->resolveSiswaId($request);
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $kode = null;
        if ($request->filled('kode_barcode')) {
            $kode = $request->input('kode_barcode');
        } elseif ($request->filled('id_barcode')) {
            $bc = \DB::table('barcode')->where('id_barcode', $request->input('id_barcode'))->first();
            $kode = $bc?->kode_barcode;
        } else {
            $bc = \DB::table('barcode')->where('id_siswa', $userId)->first();
            $kode = $bc?->kode_barcode;
        }

        if (! $kode) {
            return $this->error('Barcode tidak valid atau belum terdaftar', 404);
        }

        $hasil = $this->absensiService->catatScan($kode, null, 'scan_qr');

        if ($hasil['hasil'] === 'duplikat') {
            return $this->error($hasil['message'], 409);
        }

        if ($hasil['hasil'] === 'tidak_dikenal') {
            return $this->error('QR Barcode tidak dikenali oleh sistem', 404);
        }

        $siswa = Siswa::find($userId);

        return $this->success([
            'siswa' => $siswa?->nm_siswa ?? 'Siswa',
            'nisn' => $siswa?->nisn,
            'status' => 'Hadir',
            'keterangan' => $hasil['keterangan'] ?? 'Hadir Tepat Waktu',
            'waktu_absen' => $hasil['absen']->waktu_absen,
            'metode' => 'scan_qr',
        ], $hasil['upgrade'] ? 'Absensi diperbarui dari status Alpa' : 'Absensi registrasi berhasil', 201);
    }

    protected function resolveSiswaId(Request $request): ?int
    {
        $siswa = Siswa::first();
        $user = null;
        try {
            if (config('auth.guards.sanctum')) {
                $user = $request->user('sanctum');
            }
        } catch (\Throwable $e) {
        }
        if (! $user) {
            $user = $request->user();
        }
        if ($user && isset($user->id_siswa)) {
            return (int) $user->id_siswa;
        }

        $id = $request->input('id_siswa')
            ?? $request->query('id_siswa')
            ?? $request->query('id')
            ?? session()->get('siswa_id')
            ?? session()->get('user_id');

        if ($id) {
            return (int) $id;
        }

        return $siswa?->id_siswa;
    }
}
