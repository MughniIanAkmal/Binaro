<?php

namespace App\Http\Requests\Guru;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Aturan validasi dasar data materi, dipakai bersama
 * oleh StoreMateriRequest dan UpdateMateriRequest.
 */
abstract class MateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * URL video hanya boleh YouTube atau file MP4 langsung.
     */
    protected function aturanUrlVideo(): array
    {
        return [
            'nullable',
            'required_if:tipe_materi,video',
            'max:255',
            function (string $attribute, mixed $value, Closure $fail) {
                if ($this->input('tipe_materi') !== 'video' || ! $value) {
                    return;
                }

                $url = trim((string) $value);
                if (preg_match('#(drive\.google\.com|docs\.google\.com|google\.com/file|google\.com/drive|dropbox\.com|onedrive\.live\.com)#i', $url)) {
                    $fail('Tautan Google Drive atau penyimpanan cloud lainnya dilarang. Harap gunakan tautan YouTube atau file video langsung (.mp4).');

                    return;
                }

                $isYoutube = (bool) (preg_match('#^(https?:\/\/)?(www\.|m\.)?(youtube(?:-nocookie)?\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtu\.be\/)[a-zA-Z0-9_\-]+#i', $url) || preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts|live)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url));
                $isMp4 = (bool) preg_match('#^https?:\/\/[^\s]+\.mp4(\?[^\s]*)?$#i', $url);

                if (! $isYoutube && ! $isMp4) {
                    $fail('URL Video pembelajaran HANYA boleh berupa tautan YouTube (youtube.com / youtu.be) atau file video langsung berformat MP4 (.mp4). Link lain seperti Google Drive dilarang.');
                }
            },
        ];
    }

    /**
     * File kuis wajib berformat spreadsheet Excel / CSV.
     */
    protected function aturanFileExcel(): array
    {
        return [
            'nullable',
            'file',
            'max:5120',
            function (string $attribute, mixed $value, Closure $fail) {
                if ($this->input('tipe_materi') !== 'kuis' || ! $value) {
                    return;
                }

                $ekstensi = strtolower($value->getClientOriginalExtension());
                if (! in_array($ekstensi, ['xlsx', 'xls', 'csv'])) {
                    $fail('File kuis wajib berformat spreadsheet Excel / CSV (.xlsx, .xls, atau .csv). Format file lainnya tidak diperbolehkan.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'judul_materi.required' => 'Nama materi wajib diisi.',
            'judul_materi.min' => 'Nama materi minimal 3 karakter.',
            'judul_materi.max' => 'Nama materi maksimal 100 karakter.',
            'judul_materi.regex' => 'Nama materi hanya boleh berisi huruf, angka, dan spasi (tidak boleh mengandung simbol atau karakter khusus).',
            'url_video.required_if' => 'URL Video wajib diisi jika memilih tipe materi Video.',
            'file_pdf.mimes' => 'File materi harus berformat PDF (.pdf).',
        ];
    }
}
