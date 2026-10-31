<?php

namespace App\Http\Requests\Siswa;

class UpdateSiswaRequest extends SiswaRequest
{
    public function rules(): array
    {
        $siswa = $this->route('siswa');

        return $this->aturanDataSiswa($siswa?->id_siswa) + [
            'password' => 'nullable|string|min:6|max:100|confirmed',
        ];
    }
}
