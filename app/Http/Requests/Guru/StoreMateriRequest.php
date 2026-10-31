<?php

namespace App\Http\Requests\Guru;

class StoreMateriRequest extends MateriRequest
{
    public function rules(): array
    {
        return [
            'id_sub_bab' => 'required|exists:sub_bab,id_sub_bab',
            'judul_materi' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s]+$/u',
            ],
            'tipe_materi' => 'required|in:video,dokumen,kuis',
            'url_video' => $this->aturanUrlVideo(),
            'file_pdf' => 'nullable|required_if:tipe_materi,dokumen|file|mimes:pdf|max:10240',
            'file_excel' => $this->aturanFileExcel(),
            'isi_materi' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'file_pdf.required_if' => 'File dokumen PDF wajib diunggah jika memilih tipe Dokumen.',
        ];
    }
}
