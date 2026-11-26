<?php

namespace App\Features\Controllers\Api\Guru;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Absen;
use App\Features\Models\Guru;
use App\Features\Services\AbsensiService;
use Illuminate\Http\Request;

class AbsensiController extends ApiController
{
    public function __construct(private AbsensiService $absensiService) {}

    protected function authGuruId(Request $request): ?int
    {
        $user = $request->user();
        if ($user && isset($user->id_guru)) {
            return (int) $user->id_guru;
        }

        $id = $request->input('id_guru')
            ?? $request->query('id_guru')
            ?? session()->get('guru_id')
            ?? session()->get('user_id');

        if (! $id) {
            $firstGuru = Guru::first();

            return $firstGuru?->id_guru;
        }

        return (int) $id;
    }

    public function scanQr(Request $request)
    {
        $data = $request->validate([
            'kode_barcode' => 'required|string',
        ]);

        $guruId = $this->authGuruId($request);
        $hasil = $this->absensiService->catatScan($data['kode_barcode'], $guruId, 'scan_qr');

        if ($hasil['hasil'] === 'tidak_dikenal') {
            return $this->error('QR tidak valid', 404);
        }

        if ($hasil['hasil'] === 'duplikat') {
            return $this->error($hasil['message'], 409);
        }

        return $this->success([
            'siswa' => $hasil['siswa']->nm_siswa,
            'nisn' => $hasil['siswa']->nisn,
            'status' => 'Hadir',
            'keterangan' => $hasil['keterangan'],
            'waktu_absen' => $hasil['absen']->waktu_absen,
            'metode' => 'scan_qr',
        ], $hasil['upgrade'] ? 'Absensi diperbarui dari status Alpa' : 'Absensi registrasi berhasil', 201);
    }

    public function massAttendance(Request $request)
    {
        $data = $request->validate([
            'siswa' => 'required|array|max:50',
            'siswa.*.id_siswa' => 'required|integer|exists:siswa,id_siswa',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        $guruId = $this->authGuruId($request);
        if (! $guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $today = now()->toDateString();
        $results = [];

        foreach ($data['siswa'] as $entry) {
            $existing = Absen::where('id_siswa', $entry['id_siswa'])
                ->whereDate('tanggal', $today)
                ->first();

            if ($existing) {
                $results[] = ['id_siswa' => $entry['id_siswa'], 'status' => 'duplikat'];

                continue;
            }

            Absen::create([
                'id_guru' => $guruId,
                'id_siswa' => $entry['id_siswa'],
                'status' => $data['status'],
                'metode' => 'manual_guru',
                'waktu_absen' => now(),
                'tanggal' => $today,
            ]);

            $results[] = ['id_siswa' => $entry['id_siswa'], 'status' => 'success'];
        }

        return $this->success($results, 'Absensi massal berhasil diproses');
    }
}
