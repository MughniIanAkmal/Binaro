<?php

namespace App\Http\Controllers\Api;

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Rpp;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GuruApiController extends ApiController
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
            ?? Session::get('guru_id') 
            ?? Session::get('user_id');

        if (!$id) {
            // Default ke guru pertama jika request testing
            $firstGuru = Guru::first();
            return $firstGuru?->id_guru;
        }

        return (int) $id;
    }

    // GET /api/guru/profile
    public function profile(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $guru = Guru::find($guruId);
        if (!$guru) {
            return $this->error('Guru tidak ditemukan', 404);
        }

        return $this->success($guru, 'Data profil guru berhasil dimuat');
    }

    // POST /api/guru/profile
    public function updateProfile(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $guru = Guru::find($guruId);
        if (!$guru) {
            return $this->error('Guru tidak ditemukan', 404);
        }

        $data = $request->validate([
            'nama_guru' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'nip' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|max:100',
        ]);

        $guru->update($data);

        return $this->success($guru, 'Profil guru berhasil diperbarui');
    }

    // GET /api/guru/rpp
    public function listRpp(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $rpp = Rpp::where('id_guru', $guruId)->orderByDesc('created_at')->get();

        return $this->success($rpp, 'Daftar RPP berhasil dimuat');
    }

    // POST /api/guru/rpp
    public function createRpp(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $data = $request->validate([
            'id_rooms' => 'sometimes|integer',
            'id_mapel' => 'sometimes|integer',
            'judul_rpp' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'komponen_checklist' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        if (empty($data['id_mapel'])) {
            $data['id_mapel'] = \App\Models\MataPelajaran::value('id_mapel') ?? 17;
        }

        $data['id_guru'] = $guruId;
        $rpp = Rpp::create($data);

        return $this->success($rpp, 'RPP berhasil dibuat', 201);
    }

    // PUT /api/guru/rpp/{id}
    public function editRpp(Request $request, $id)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $rpp = Rpp::where('id_rpp', $id)->where('id_guru', $guruId)->first();
        if (!$rpp) {
            return $this->error('RPP tidak ditemukan', 404);
        }

        $data = $request->validate([
            'judul_rpp' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'komponen_checklist' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        $rpp->update($data);

        return $this->success($rpp, 'RPP berhasil diperbarui');
    }

    // DELETE /api/guru/rpp/{id}
    public function deleteRpp(Request $request, $id)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $rpp = Rpp::where('id_rpp', $id)->where('id_guru', $guruId)->first();
        if (!$rpp) {
            return $this->error('RPP tidak ditemukan', 404);
        }

        $rpp->delete();

        return $this->success(null, 'RPP berhasil dihapus');
    }

    // GET /api/guru/siswa
    public function listSiswa(Request $request)
    {
        $guruId = $this->authGuruId($request);
        if (!$guruId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $siswa = Siswa::with(['kelas', 'barcode'])->get();

        return $this->success($siswa, 'Data siswa berhasil dimuat');
    }

    // POST /api/guru/absensi/scan-qr
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

    // POST /api/guru/absen-massal
    public function massAttendance(Request $request)
    {
        $data = $request->validate([
            'siswa' => 'required|array|max:50',
            'siswa.*.id_siswa' => 'required|integer|exists:siswa,id_siswa',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        $guruId = $this->authGuruId($request);
        if (!$guruId) {
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
