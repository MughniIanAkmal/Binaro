<?php

namespace App\Http\Requests\Guru;

class StoreUjianRequest extends UjianRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'soal' => 'nullable|array',
            'soal.*.pertanyaan' => 'nullable|string|max:2000',
            'soal.*.opsi_a' => 'nullable|string|max:255',
            'soal.*.opsi_b' => 'nullable|string|max:255',
            'soal.*.opsi_c' => 'nullable|string|max:255',
            'soal.*.opsi_d' => 'nullable|string|max:255',
            'soal.*.kunci_jawaban' => 'nullable|in:A,B,C,D',
            'soal.*.bobot_nilai' => 'nullable|numeric|min:1|max:100',
            'soal.*.tingkat_kesulitan' => 'nullable|in:mudah,sedang,susah,sulit',
            'soal.*.gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'tingkat_level.required' => 'Tingkatan level ujian wajib dipilih (mudah, sedang, atau susah).',
            'durasi_menit.required' => 'Durasi ujian wajib diisi (minimal 5 menit).',
        ];
    }
}
