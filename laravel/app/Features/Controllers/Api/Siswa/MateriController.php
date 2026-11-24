<?php

namespace App\Features\Controllers\Api\Siswa;

use App\Features\Controllers\Api\ApiController;
use App\Features\Models\Materi;
use App\Features\Models\SubBab;
use App\Features\Models\Quiz;
use Illuminate\Http\Request;

class MateriController extends ApiController
{
    public function materiList(Request $request, $id)
    {
        $subBab = SubBab::with(['bab.mataPelajaran'])->find($id);
        if (!$subBab) {
            $subBab = SubBab::with(['bab.mataPelajaran'])->whereHas('materi')->first()
                ?? SubBab::with(['bab.mataPelajaran'])->first();
        }
        if (!$subBab) {
            return $this->error('Sub Bab tidak ditemukan', 404);
        }

        $materis = Materi::where('id_sub_bab', $subBab->id_sub_bab)->orderBy('id_materi')->get();

        $materiList = [];
        foreach ($materis as $idx => $m) {
            $nomor = $idx + 1;
            $tipe = $m->tipe_materi ?: 'dokumen';

            $deskripsi = $m->isi_materi ? substr(strip_tags($m->isi_materi), 0, 140) : null;
            if (!$deskripsi) {
                $deskripsi = match ($tipe) {
                    'kuis' => 'Silakan kerjakan quiz untuk menguji pemahaman materi ini.',
                    'video' => 'Tonton video penjelasan materi pembelajaran interaktif ini.',
                    default => 'Pelajari dokumen rangkuman materi pembelajaran ini.',
                };
            }

            $materiList[] = [
                'id_materi' => $m->id_materi,
                'nomor' => $nomor,
                'tipe_materi' => $tipe,
                'id_quiz' => $m->id_quiz,
                'badge' => $tipe === 'kuis' ? "KUIS {$nomor}" : "MATERI {$nomor}",
                'info_meta' => $tipe === 'video' ? 'Video Belajar' : ($tipe === 'kuis' ? 'Kuis Interaktif' : 'Dokumen PDF'),
                'judul_materi' => $m->judul_materi ?: 'Materi Pembelajaran',
                'deskripsi' => $deskripsi,
                'warna' => $tipe === 'kuis' ? 'orange' : (($nomor % 2 == 0) ? 'orange' : 'blue'),
                'url_video' => $m->url_video,
                'has_thumbnail' => !empty($m->file_pdf),
            ];
        }

        $babNama = $subBab->bab?->nama_bab ?? 'Bab';
        $subNama = $subBab->nama_sub_bab ?? 'Sub Bab';

        return $this->success([
            'breadcrumb' => "{$babNama} > {$subNama}",
            'sub_bab' => [
                'id_sub_bab' => $subBab->id_sub_bab,
                'nama' => $subBab->nama_sub_bab,
            ],
            'materis' => $materiList,
        ], 'Daftar materi berhasil dimuat');
    }

    public function materiDetail(Request $request, $id)
    {
        $materi = Materi::with(['subBab.bab.mataPelajaran'])->find($id);
        if (!$materi) {
            return $this->error('Materi belum diunggah atau tidak ditemukan di database', 404);
        }

        $subBab = $materi->subBab;
        $bab = $subBab?->bab;
        $mapel = $bab?->mataPelajaran;

        $hasVideo = !empty($materi->url_video);
        $hasPdf = !empty($materi->file_pdf);
        $hasText = !empty(trim(strip_tags((string)$materi->isi_materi)));

        $storageUrl = $request->root() . '/storage/' . $materi->file_pdf;
        $downloadUrl = $request->root() . '/storage/' . $materi->file_pdf . '?download=1';

        return $this->success([
            'id_materi' => $materi->id_materi,
            'tipe_materi' => $materi->tipe_materi ?: ($hasPdf ? 'dokumen' : ($hasVideo ? 'video' : 'teks')),
            'judul_materi' => $materi->judul_materi,
            'id_quiz' => $materi->id_quiz,
            'breadcrumb' => [
                'mapel' => $mapel?->nama_mapel ?? 'Mata Pelajaran',
                'bab' => $bab?->nama_bab ?? 'Bab',
                'sub_bab' => $subBab?->nama_sub_bab ?? 'Sub Bab',
            ],
            'video' => $hasVideo ? [
                'judul' => $materi->judul_materi,
                'url' => $materi->youtube_watch_url ?: $materi->url_video,
            ] : null,
            'pdf' => $hasPdf ? [
                'file_pdf' => basename($materi->file_pdf),
                'url' => $storageUrl,
                'download_url' => $downloadUrl,
            ] : null,
            'konten' => $hasText ? [
                'judul' => $materi->judul_materi,
                'isi' => $materi->isi_materi,
            ] : null,
            'kuis_lanjutan' => $materi->id_quiz ? [
                'id_quiz' => $materi->id_quiz,
                'judul' => 'Lanjut ke Kuis / Latihan',
                'jumlah_soal' => Quiz::find($materi->id_quiz)?->soal()->count() ?? 0,
            ] : null,
        ], 'Detail materi berhasil dimuat');
    }

    protected function resolveSiswaId(Request $request): ?int
    {
        $id = $request->input('id_siswa') ?? $request->query('id_siswa') ?? $request->query('id');
        if ($id) return (int) $id;
        return \App\Features\Models\Siswa::first()?->id_siswa;
    }
}
