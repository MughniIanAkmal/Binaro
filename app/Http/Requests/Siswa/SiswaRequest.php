<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Aturan validasi dasar data siswa, dipakai bersama
 * oleh StoreSiswaRequest dan UpdateSiswaRequest.
 */
abstract class SiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan umum data siswa; $idSiswa diisi saat update agar
     * data unik milik siswa tersebut tidak dianggap duplikat.
     */
    protected function aturanDataSiswa(?int $idSiswa = null): array
    {
        $uniqueNisn = Rule::unique('siswa', 'nisn');
        $uniqueEmail = Rule::unique('siswa', 'email');
        $uniqueUsername = Rule::unique('siswa', 'username');

        if ($idSiswa !== null) {
            $uniqueNisn->ignore($idSiswa, 'id_siswa');
            $uniqueEmail->ignore($idSiswa, 'id_siswa');
            $uniqueUsername->ignore($idSiswa, 'id_siswa');
        }

        return [
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'nis' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/', $uniqueNisn],
            'email' => ['required', 'string', 'email:rfc', 'max:100', 'regex:/@/', $uniqueEmail],
            'no_hp' => ['nullable', 'string', 'max:12', 'regex:/^[0-9]*$/'],
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:500',
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', $uniqueUsername],
            'id_mapel' => 'nullable|exists:mata_pelajaran,id_mapel',
            'id_rooms' => 'nullable|exists:kelas,id_rooms',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama siswa wajib diisi.',
            'nama.max' => 'Nama siswa maksimal 100 karakter.',
            'nama.regex' => 'Nama lengkap hanya boleh berisi huruf dan spasi, tanpa angka atau simbol.',
            'nis.required' => 'NISN wajib diisi.',
            'nis.max' => 'NISN maksimal 15 angka.',
            'nis.regex' => 'NISN hanya boleh berisi angka (0-9).',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus valid dan mengandung tanda @, contoh: nama@email.com.',
            'email.regex' => 'Email harus mengandung tanda @, contoh: nama@email.com.',
            'email.unique' => 'Email sudah dipakai siswa lain.',
            'no_hp.max' => 'No. HP maksimal 12 angka.',
            'no_hp.regex' => 'No. HP hanya boleh berisi angka (0-9).',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'password.max' => 'Password maksimal 100 karakter.',
        ];
    }
}
