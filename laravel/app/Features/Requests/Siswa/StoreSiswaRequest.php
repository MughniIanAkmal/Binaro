<?php

namespace App\Features\Requests\Siswa;

class StoreSiswaRequest extends SiswaRequest
{
    public function rules(): array
    {
        return $this->aturanDataSiswa() + [
            'password' => 'required|string|min:6|max:100|confirmed',
        ];
    }
}
