<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Aturan validasi dasar data ujian online, dipakai bersama
 * oleh StoreUjianRequest dan UpdateUjianRequest.
 */
abstract class UjianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul_quiz' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/u',
            ],
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'tingkat_level' => 'required|in:mudah,sedang,susah',
            'durasi_menit' => 'required|integer|min:5|max:300',
            'target_tipe' => 'required|in:semua,pilihan',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'exists:siswa,id_siswa',
            'deskripsi' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'judul_quiz.required' => 'Judul ujian wajib diisi.',
            'judul_quiz.min' => 'Judul ujian minimal 3 karakter.',
            'judul_quiz.max' => 'Judul ujian maksimal 100 karakter.',
            'judul_quiz.regex' => 'Judul ujian hanya boleh berisi huruf dan spasi (tidak boleh mengandung angka maupun simbol).',
            'id_mapel.required' => 'Mata pelajaran wajib dipilih.',
            'tingkat_level.required' => 'Tingkatan level ujian wajib dipilih.',
            'durasi_menit.required' => 'Durasi ujian wajib diisi.',
            'target_tipe.required' => 'Pilih sasaran peserta ujian (Semua Siswa atau Siswa Tertentu).',
            'deskripsi.max' => 'Deskripsi ujian maksimal 1000 karakter.',
        ];
    }
}
