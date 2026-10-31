<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Rpp;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GuruApiController extends Controller
{
    public function __construct(private AbsensiService $absensiService) {}

    protected function authGuruId()
    {
        if (Session::has('user_type') && Session::get('user_type') !== 'guru' && ! Session::has('guru_id')) {
            return null;
        }

        return Session::get('guru_id') ?? Session::get('user_id');
    }

    // GET /api/guru/profile
    public function profile(Request $request)
    {
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

        $guru = DB::table('guru')->where('id_guru', $guruId)->first();
        if (! $guru) {
            return response()->json(['success' => false, 'message' => 'Guru tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $guru]);
    }

    // POST /api/guru/profile
    public function updateProfile(Request $request)
    {
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

        $data = $request->validate([
            'nama_guru' => 'sometimes|string|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'nip' => 'sometimes|string|max:50',
        ]);

        DB::table('guru')->where('id_guru', $guruId)->update($data);
        $guru = DB::table('guru')->where('id_guru', $guruId)->first();

        return response()->json(['success' => true, 'message' => 'Profil diperbarui', 'data' => $guru]);
    }

    // GET /api/guru/rpp
    public function listRpp(Request $request)
    {
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

        $rpp = Rpp::where('id_guru', $guruId)->orderByDesc('created_at')->get();

        return response()->json(['success' => true, 'data' => $rpp]);
    }

    // POST /api/guru/rpp
    public function createRpp(Request $request)
    {
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

        $data = $request->validate([
            'id_rooms' => 'sometimes|integer',
            'id_mapel' => 'sometimes|integer',
            'judul_rpp' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'komponen_checklist' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        $data['id_guru'] = $guruId;
        $rpp = Rpp::create($data);

        return response()->json(['success' => true, 'message' => 'RPP dibuat', 'data' => $rpp], 201);
    }

    // PUT /api/guru/rpp/{id}
    public function editRpp(Request $request, $id)
    {
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

        $rpp = Rpp::where('id_rpp', $id)->where('id_guru', $guruId)->first();
        if (! $rpp) {
            return response()->json(['success' => false, 'message' => 'RPP tidak ditemukan'], 404);
        }

        $data = $request->validate([
            'judul_rpp' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'komponen_checklist' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        $rpp->update($data);

        return response()->json(['success' => true, 'message' => 'RPP diperbarui', 'data' => $rpp]);
    }

    // DELETE /api/guru/rpp/{id}
    public function deleteRpp($id)
    {
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

        $rpp = Rpp::where('id_rpp', $id)->where('id_guru', $guruId)->first();
        if (! $rpp) {
            return response()->json(['success' => false, 'message' => 'RPP tidak ditemukan'], 404);
        }

        $rpp->delete();

        return response()->json(['success' => true, 'message' => 'RPP dihapus']);
    }

    // GET /api/guru/siswa
    public function listSiswa(Request $request)
    {
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

        $guru = DB::table('guru')->where('id_guru', $guruId)->first();
        if (! $guru) {
            return response()->json(['success' => false, 'message' => 'Guru tidak ditemukan'], 404);
        }

        $siswa = Siswa::with(['kelas', 'barcode'])->get();

        return response()->json(['success' => true, 'data' => $siswa]);
    }

    // POST /api/guru/absensi/scan-qr
    public function scanQr(Request $request)
    {
        $data = $request->validate([
            'kode_barcode' => 'required|string',
        ]);

        $hasil = $this->absensiService->catatScan($data['kode_barcode'], $this->authGuruId(), 'scan_qr');

        if ($hasil['hasil'] === 'tidak_dikenal') {
            return response()->json([
                'success' => false,
                'message' => 'QR tidak valid',
            ], 404);
        }

        if ($hasil['hasil'] === 'duplikat') {
            return response()->json([
                'success' => false,
                'message' => $hasil['message'],
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => $hasil['upgrade'] ? 'Absensi diperbarui dari status Alpa' : 'Absensi registrasi',
            'data' => [
                'siswa' => $hasil['siswa']->nm_siswa,
                'nisn' => $hasil['siswa']->nisn,
                'status' => 'Hadir',
                'keterangan' => $hasil['keterangan'],
                'waktu_absen' => $hasil['absen']->waktu_absen,
                'metode' => 'scan_qr',
            ],
        ], 201);
    }

    // POST /api/guru/absen-massal
    public function massAttendance(Request $request)
    {
        $data = $request->validate([
            'siswa' => 'required|array|max:50',
            'siswa.*.id_siswa' => 'required|integer|exists:siswa,id_siswa',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        $today = now()->toDateString();
        $guruId = $this->authGuruId();
        if (! $guruId) {
            return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
        }

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

        return response()->json(['success' => true, 'message' => 'Absensi massal diproses', 'data' => $results]);
    }
}
