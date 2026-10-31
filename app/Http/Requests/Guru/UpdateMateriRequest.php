<?php

namespace App\Http\Requests\Guru;

class UpdateMateriRequest extends MateriRequest
{
    public function rules(): array
    {
        return [
            'judul_materi' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'tipe_materi' => 'required|in:video,dokumen,kuis',
            'url_video' => $this->aturanUrlVideo(),
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'file_excel' => $this->aturanFileExcel(),
        ];
    }
}
