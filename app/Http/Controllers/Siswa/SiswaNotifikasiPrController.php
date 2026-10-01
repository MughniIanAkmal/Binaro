<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pr;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SiswaNotifikasiPrController extends Controller
{
    /**
     * Pastikan siswa aktif diambil dari session, atau fallback ke siswa pertama untuk preview/testing.
     */
    private function getSiswa()
    {
        $siswaId = session('user_id');
        $userType = session('user_type');

        if ($siswaId && $userType === 'siswa') {
            $siswa = Siswa::with('kelas')->find($siswaId);
            if ($siswa) {
                return $siswa;
            }
        }

        // Jika diakses oleh guru/admin saat pengujian fitur portal siswa,
        // gunakan data siswa pertama agar tetap bisa melihat dan memfilter notifikasi siswa tanpa terlempar ke portal guru
        return Siswa::with('kelas')->first();
    }

    /**
     * Daftar semua notifikasi PR milik siswa yang sedang login.
     */
    public function index(Request $request)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $filter = $request->query('filter', 'semua'); // semua | belum_dibaca | sudah_dibaca
        $search = $request->query('search');

        $query = Notifikasi::with(['pr.mataPelajaran', 'guru'])
            ->where('id_siswa', $siswa->id_siswa)
            ->whereNotNull('id_pr');

        if ($filter === 'belum_dibaca') {
            $query->where('status_baca', 0);
        } elseif ($filter === 'sudah_dibaca') {
            $query->where('status_baca', 1);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pesan', 'like', "%{$search}%")
                  ->orWhereHas('pr', fn($p) => $p->where('nama_pr', 'like', "%{$search}%"))
                  ->orWhereHas('pr.mataPelajaran', fn($m) => $m->where('nama_mapel', 'like', "%{$search}%"));
            });
        }

        $notifikasis = $query->latest('id_notifikasi')->paginate(10)->withQueryString();

        // KPI Stats
        $totalNotif     = Notifikasi::where('id_siswa', $siswa->id_siswa)->whereNotNull('id_pr')->count();
        $belumDibaca    = Notifikasi::where('id_siswa', $siswa->id_siswa)->whereNotNull('id_pr')->where('status_baca', 0)->count();
        $sudahDibaca    = Notifikasi::where('id_siswa', $siswa->id_siswa)->whereNotNull('id_pr')->where('status_baca', 1)->count();
        $prAktif        = Notifikasi::where('id_siswa', $siswa->id_siswa)
                            ->whereNotNull('id_pr')
                            ->whereHas('pr', fn($p) => $p->where('tgl_tenggat', '>=', Carbon::now()))
                            ->count();

        return view('siswa.notifikasi_pr.index', compact(
            'siswa', 'notifikasis', 'totalNotif',
            'belumDibaca', 'sudahDibaca', 'prAktif',
            'filter', 'search'
        ));
    }

    /**
     * Detail satu notifikasi PR. Otomatis menandai sebagai sudah dibaca saat dibuka.
     */
    public function show($id)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $notifikasi = Notifikasi::with(['pr.mataPelajaran', 'pr.guru', 'guru'])
            ->where('id_siswa', $siswa->id_siswa)
            ->whereNotNull('id_pr')
            ->findOrFail($id);

        // Otomatis tandai baca saat halaman detail dibuka
        if (!$notifikasi->status_baca) {
            $notifikasi->update(['status_baca' => 1]);
        }

        // Notifikasi lain untuk PR yang sama (dari guru yang sama)
        $notifikasiLain = Notifikasi::with('pr.mataPelajaran')
            ->where('id_siswa', $siswa->id_siswa)
            ->whereNotNull('id_pr')
            ->where('id_pr', $notifikasi->id_pr)
            ->where('id_notifikasi', '!=', $notifikasi->id_notifikasi)
            ->latest('id_notifikasi')
            ->take(5)
            ->get();

        return view('siswa.notifikasi_pr.show', compact('siswa', 'notifikasi', 'notifikasiLain'));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca (via POST).
     */
    public function tandaiBaca($id)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            return redirect()->route('login');
        }

        try {
            $notifikasi = Notifikasi::where('id_siswa', $siswa->id_siswa)
                ->whereNotNull('id_pr')
                ->findOrFail($id);

            if ($notifikasi->status_baca) {
                return back()->with('info', 'Notifikasi ini sudah ditandai sebagai dibaca sebelumnya.');
            }

            $notifikasi->update(['status_baca' => 1]);
            return back()->with('success', 'Notifikasi berhasil ditandai sebagai sudah dibaca.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menandai notifikasi. Silakan coba kembali.');
        }
    }

    /**
     * Tandai semua notifikasi milik siswa yang login sebagai sudah dibaca.
     */
    public function tandaiBacaSemua()
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            return redirect()->route('login');
        }

        try {
            $jumlah = Notifikasi::where('id_siswa', $siswa->id_siswa)
                ->whereNotNull('id_pr')
                ->where('status_baca', 0)
                ->count();

            if ($jumlah === 0) {
                return back()->with('info', 'Semua notifikasi sudah dalam status dibaca.');
            }

            Notifikasi::where('id_siswa', $siswa->id_siswa)
                ->whereNotNull('id_pr')
                ->where('status_baca', 0)
                ->update(['status_baca' => 1]);

            return back()->with('success', "{$jumlah} notifikasi berhasil ditandai sebagai sudah dibaca.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses permintaan. Silakan coba kembali.');
        }
    }
}
