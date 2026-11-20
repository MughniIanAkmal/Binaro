<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Pengaturan semester aktif SATU sekolah (bukan per kelas).
     * Satu sekolah selalu berjalan di satu semester yang sama, jadi
     * cukup satu saklar global agar tidak ada kelas yang tertinggal
     * atau berbeda sendiri karena lupa diubah.
     */
    public function index()
    {
        $semesterAktif = (int) Pengaturan::ambil('semester_aktif', 1);
        $kelasList = Kelas::orderBy('id_rooms')->get();

        return view('admin.semester.index', compact('semesterAktif', 'kelasList'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'semester_aktif' => 'required|in:1,2',
        ]);

        Pengaturan::simpan('semester_aktif', (int) $data['semester_aktif']);

        $label = (int) $data['semester_aktif'] === 2 ? 'Semester Genap' : 'Semester Ganjil';

        return back()->with('success', "Semester aktif sekolah berhasil diubah ke {$label}.");
    }
}
