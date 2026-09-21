<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $siswas = Siswa::with('barcode', 'kelas')
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama', 'like', "%{$q}%")
                      ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->when($request->kelas, fn ($query, $kelas) => $query->whereHas('kelas', function ($q) use ($kelas) {
                $q->where('nama_kelas', $kelas);
            }))
            ->orderBy('id_kelas')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.qr-siswa.index', [
            'siswas'      => $siswas,
            'total'       => Siswa::count(),
            'sudah'       => Barcode::count(),
            'daftarKelas' => Kelas::query()
                ->select('nama_kelas')
                ->distinct()
                ->orderBy('nama_kelas')
                ->pluck('nama_kelas'),
        ]);
    }

    /** Buat QR untuk satu siswa (aman ditekan berulang: tidak membuat ganda). */
    public function store(Siswa $siswa)
    {
        Barcode::firstOrCreate(
            ['id_siswa' => $siswa->id_siswa],
            ['kode_barcode' => Barcode::buatKodeUnik()]
        );

        return back()->with('success', "QR code untuk {$siswa->nama_siswa} berhasil dibuat.");
    }

    /** Buat QR untuk semua siswa yang belum punya. */
    public function storeAll()
    {
        $dibuat = 0;

        Siswa::doesntHave('barcode')->get()->each(function ($siswa) use (&$dibuat) {
            Barcode::create([
                'id_siswa' => $siswa->id_siswa,
                'kode_barcode' => Barcode::buatKodeUnik(),
            ]);
            $dibuat++;
        });

        return back()->with('success', "{$dibuat} QR code berhasil dibuat.");
    }

    /** Ganti kode (misal kartu hilang). Kode lama otomatis tidak berlaku. */
    public function regenerate(Siswa $siswa)
    {
        Barcode::updateOrCreate(
            ['id_siswa' => $siswa->id_siswa],
            ['kode_barcode' => Barcode::buatKodeUnik()]
        );

        return back()->with('success', "Kode baru untuk {$siswa->nama_siswa} dibuat. Kartu lama tidak bisa dipakai lagi.");
    }

    /** Kartu satu siswa. */
    public function cetak(Siswa $siswa)
    {
        $siswa->load('barcode');
        abort_unless($siswa->barcode, 404, 'Siswa ini belum punya QR code.');

        return view('admin.qr-siswa.cetak', ['siswas' => collect([$siswa])]);
    }

    /** Kartu banyak siswa sekaligus (opsional per kelas: ?kelas=3A). */
    public function cetakKelas(Request $request)
    {
        $siswas = Siswa::has('barcode')
            ->with('barcode', 'kelas')
            ->when($request->kelas, fn ($query, $kelas) => $query->whereHas('kelas', function ($q) use ($kelas) {
                $q->where('nama_kelas', $kelas);
            }))
            ->orderBy('id_kelas')
            ->orderBy('nama')
            ->get();

        return view('admin.qr-siswa.cetak', compact('siswas'));
    }
}
