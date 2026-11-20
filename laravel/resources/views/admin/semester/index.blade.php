@extends('layouts.app')

@section('page_title', 'Pengaturan Semester')
@section('page_subtitle', 'Tentukan semester yang sedang berjalan untuk tiap kelas. Aplikasi siswa hanya menampilkan bab pada semester aktif kelasnya.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
            <i class="fas fa-circle-check mr-1.5"></i>{{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.semester.update') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-calendar-days text-primary"></i> Semester Aktif per Kelas
            </h3>
            <span class="text-[11px] text-slate-500">Perubahan langsung berlaku di aplikasi siswa.</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($kelasList as $kelas)
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                            <i class="fas fa-school text-sm"></i>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-slate-800">{{ $kelas->pararel }}</p>
                            <p class="text-[11px] text-slate-500">{{ $kelas->siswas()->count() }} siswa</p>
                        </div>
                    </div>
                    <select name="semester_aktif[{{ $kelas->id_rooms }}]" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary">
                        <option value="1" {{ (int) ($kelas->semester_aktif ?? 1) === 1 ? 'selected' : '' }}>Semester Ganjil</option>
                        <option value="2" {{ (int) ($kelas->semester_aktif ?? 1) === 2 ? 'selected' : '' }}>Semester Genap</option>
                    </select>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-xs text-slate-500">
                    Belum ada data kelas.
                </div>
            @endforelse
        </div>

        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="fas fa-save mr-1.5"></i>Simpan Pengaturan
            </button>
        </div>
    </form>

    <div class="px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-start gap-2">
        <i class="fas fa-triangle-exclamation mt-0.5"></i>
        <span>Bab di aplikasi siswa diatur oleh guru lewat menu Kelola Materi (tiap bab termasuk Semester Ganjil atau Genap). Mengganti semester aktif di halaman ini akan langsung mengganti bab yang tampil untuk seluruh siswa di kelas tersebut.</span>
    </div>
</div>
@endsection
