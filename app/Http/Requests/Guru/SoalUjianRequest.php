<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi satu soal ujian, dipakai bersama oleh
 * storeSoal dan updateSoal pada UjianController.
 */
class SoalUjianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pertanyaan' => 'required|string|max:2000',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'opsi_a' => 'required|string|max:255',
            'opsi_b' => 'required|string|max:255',
            'opsi_c' => 'required|string|max:255',
            'opsi_d' => 'required|string|max:255',
            'kunci_jawaban' => 'required|in:A,B,C,D',
            'bobot_nilai' => 'required|numeric|min:1|max:100',
            'tingkat_kesulitan' => 'nullable|in:mudah,sedang,susah,sulit',
        ];
    }

    public function messages(): array
    {
        return [
            'pertanyaan.required' => 'Teks pertanyaan soal wajib diisi.',
            'pertanyaan.max' => 'Teks pertanyaan soal maksimal 2000 karakter.',
            'gambar.image' => 'File pendukung harus berupa gambar valid (JPEG, PNG, WEBP, GIF).',
            'gambar.max' => 'Ukuran gambar maksimal 2MB.',
            'opsi_a.required' => 'Pilihan A wajib diisi.',
            'opsi_a.max' => 'Pilihan A maksimal 255 karakter.',
            'opsi_b.required' => 'Pilihan B wajib diisi.',
            'opsi_b.max' => 'Pilihan B maksimal 255 karakter.',
            'opsi_c.required' => 'Pilihan C wajib diisi.',
            'opsi_c.max' => 'Pilihan C maksimal 255 karakter.',
            'opsi_d.required' => 'Pilihan D wajib diisi.',
            'opsi_d.max' => 'Pilihan D maksimal 255 karakter.',
            'kunci_jawaban.required' => 'Kunci jawaban wajib dipilih.',
            'bobot_nilai.required' => 'Bobot nilai soal wajib ditentukan.',
        ];
    }
}
