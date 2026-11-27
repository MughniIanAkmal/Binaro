<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Notifikasi;
use App\Features\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotifikasiController extends ApiController
{
    public function notifikasiList(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        if (! $siswaId) {
            return $this->error('User tidak terautentikasi', 401);
        }
        $siswa = Siswa::find($siswaId);

        if (! $siswa) {
            return $this->success(['belum_dibaca' => 0, 'notifikasi' => []], 'Belum ada notifikasi');
        }

        $daftar = Notifikasi::with(['pr.mataPelajaran', 'pr.guru'])
            ->where('id_siswa', $siswa->id_siswa)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $hariIni = Carbon::today();
        $result = $daftar->map(function ($n) use ($hariIni) {
            $pr = $n->pr;
            $prData = null;

            if ($pr) {
                $tenggat = Carbon::parse($pr->tgl_tenggat);
                $sisaHari = (int) $hariIni->diffInDays($tenggat->copy()->startOfDay(), false);
                $labelTenggat = match (true) {
                    $sisaHari < 0 => 'Terlewat '.abs($sisaHari).' hari',
                    $sisaHari === 0 => 'Hari ini',
                    $sisaHari === 1 => 'Besok',
                    default => $sisaHari.' hari lagi',
                };

                $prData = [
                    'id_pr' => $pr->id_pr,
                    'nama_pr' => $pr->nama_pr,
                    'deskripsi' => $pr->deskripsi,
                    'nama_mapel' => $pr->mataPelajaran?->nama_mapel,
                    'nama_guru' => $pr->guru?->nama_guru,
                    'tgl_tenggat' => $tenggat->toIso8601String(),
                    'tenggat_format' => $tenggat->translatedFormat('l, d M Y').' • '.$tenggat->format('H:i'),
                    'sisa_hari' => $sisaHari,
                    'label_tenggat' => $labelTenggat,
                    'mendesak' => $sisaHari <= 1,
                ];
            }

            return [
                'id_notifikasi' => $n->id_notifikasi,
                'pesan' => $n->pesan,
                'status_baca' => (bool) $n->status_baca,
                'dibuat_pada' => $n->created_at?->translatedFormat('d M Y • H:i'),
                'pr' => $prData,
            ];
        })->values();

        return $this->success([
            'belum_dibaca' => $daftar->where('status_baca', false)->count(),
            'notifikasi' => $result,
        ], 'Daftar notifikasi berhasil dimuat');
    }

    public function notifikasiBaca(Request $request)
    {
        $siswaId = $this->resolveSiswaId($request);
        if (! $siswaId) {
            return $this->error('User tidak terautentikasi', 401);
        }
        $siswa = Siswa::find($siswaId);

        if ($siswa) {
            Notifikasi::where('id_siswa', $siswa->id_siswa)
                ->where('status_baca', false)
                ->update(['status_baca' => true]);
        }

        return $this->success(['belum_dibaca' => 0], 'Semua notifikasi ditandai sudah dibaca');
    }
}
