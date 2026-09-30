<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SiswaApiController extends ApiController
{
    // GET /api/siswa/profile
    public function profile(Request $request)
    {
        $siswaId = $request->query('id');

        if ($siswaId) {
            $siswa = Siswa::with(['barcode', 'kelas', 'absens' => function ($query) {
                $query->orderByDesc('tanggal')->orderByDesc('waktu_absen');
            }])->find($siswaId);
        } else {
            $userId = Session::get('user_id');
            if (! $userId) {
                return $this->error('User tidak terautentikasi', 401);
            }

            $siswa = Siswa::with(['barcode', 'kelas', 'absens' => function ($query) {
                $query->orderByDesc('tanggal')->orderByDesc('waktu_absen');
            }])->find($userId);
        }

        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        return $this->success($siswa);
    }

    // POST /api/siswa/profile
    public function updateProfile(Request $request)
    {
        $userId = Session::get('user_id');
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $siswa = Siswa::find($userId);
        if (! $siswa) {
            return $this->error('Siswa tidak ditemukan', 404);
        }

        $data = $request->validate([
            'nm_siswa' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'foto_profil' => 'sometimes|url',
        ]);

        $siswa->update($data);

        return $this->success($siswa, 'Profil berhasil diperbarui');
    }

    // GET /api/siswa/absensi-hari-ini
    public function todayAttendance(Request $request)
    {
        $userId = Session::get('user_id');
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $today = now()->toDateString();
        $absen = Absen::where('id_siswa', $userId)
            ->where('tanggal', $today)
            ->first();

        return $this->success($absen);
    }

    // POST /api/siswa/absensi-scan
    public function selfScan(Request $request)
    {
        $data = $request->validate([
            'id_barcode' => 'required|exists:barcode,id_barcode',
        ]);

        $userId = Session::get('user_id');
        if (! $userId) {
            return $this->error('User tidak terautentikasi', 401);
        }

        $barcode = DB::table('barcode')
            ->where('id_barcode', $data['id_barcode'])
            ->where('is_active', true)
            ->first();

        if (! $barcode) {
            return $this->error('QR tidak valid', 404);
        }

        if ($barcode->id_siswa != $userId) {
            return $this->error('QR tidak valid', 404);
        }

        $today = now()->toDateString();
        $absen = Absen::where('id_siswa', $userId)
            ->where('tanggal', $today)
            ->first();

        if ($absen) {
            return $this->error(
                "Siswa sudah absen {$absen->status} pada {$absen->tanggal}",
                409
            );
        }

        $guruId = Session::get('guru_id') ?? DB::table('guru')->value('id_guru');
        if (! $guruId) {
            $guruId = DB::table('guru')->insertGetId([
                'nip' => 'GURU-DEMO',
                'nama_guru' => 'Guru Demo',
                'created_at' => now(),
            ], 'id_guru');
        }

        $absen = Absen::create([
            'id_guru' => $guruId,
            'id_siswa' => $userId,
            'id_barcode' => $barcode->id_barcode,
            'status' => 'Hadir',
            'metode' => 'scan',
            'waktu_absen' => now(),
            'tanggal' => $today,
        ]);

        $siswa = Siswa::find($userId);

        return $this->success([
            'siswa' => $siswa->nm_siswa,
            'nisn' => $siswa->nisn,
            'status' => 'Hadir',
            'waktu_absen' => $absen->waktu_absen,
            'metode' => 'scan',
        ], 'Absensi registrasi', 201);
    }
}