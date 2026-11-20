<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Pengaturan semester aktif per kelas.
     * Aplikasi siswa hanya menampilkan bab pada semester aktif kelasnya.
     */
    public function index()
    {
        $kelasList = Kelas::orderBy('id_rooms')->get();

        return view('admin.semester.index', compact('kelasList'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'semester_aktif' => 'required|array',
            'semester_aktif.*' => 'required|in:1,2',
        ]);

        foreach ($data['semester_aktif'] as $idRooms => $semester) {
            Kelas::where('id_rooms', $idRooms)->update([
                'semester_aktif' => (int) $semester,
            ]);
        }

        return back()->with('success', 'Semester aktif berhasil disimpan.');
    }
}
