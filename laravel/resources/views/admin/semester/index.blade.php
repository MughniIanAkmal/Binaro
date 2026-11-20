@extends('layouts.app')

@section('page_title', 'Pengaturan Semester')
@section('page_subtitle', 'Satu semester aktif untuk seluruh sekolah. Aplikasi siswa hanya menampilkan bab pada semester yang sedang berjalan ini.')

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
                <i class="fas fa-calendar-days text-primary"></i> Semester Aktif Sekolah
            </h3>
            <span class="text-[11px] text-slate-500">Berlaku untuk seluruh {{ $kelasList->count() }} kelas &bull; {{ $kelasList->sum(fn($k) => $k->siswas()->count()) }} siswa</span>
        </div>

        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="cursor-pointer">
                <input type="radio" name="semester_aktif" value="1" class="peer sr-only" {{ $semesterAktif === 1 ? 'checked' : '' }}>
                <span class="block px-4 py-4 rounded-xl border-2 border-slate-200 peer-checked:border-primary peer-checked:bg-primary/5 transition">
                    <span class="text-sm font-bold text-slate-800">Semester Ganjil</span>
                    <span class="block text-[11px] text-slate-500 mt-1">Awal tahun ajaran (sekitar Juli &ndash; Desember).</span>
                </span>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="semester_aktif" value="2" class="peer sr-only" {{ $semesterAktif === 2 ? 'checked' : '' }}>
                <span class="block px-4 py-4 rounded-xl border-2 border-slate-200 peer-checked:border-primary peer-checked:bg-primary/5 transition">
                    <span class="text-sm font-bold text-slate-800">Semester Genap</span>
                    <span class="block text-[11px] text-slate-500 mt-1">Paruh kedua tahun ajaran (sekitar Januari &ndash; Juni).</span>
                </span>
            </label>
        </div>

        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-4">
            <p class="text-[11px] text-slate-500">Seluruh kelas &mdash;
                @foreach($kelasList as $kelas){{ $kelas->pararel }}@if(!$loop->last), @endif @endforeach
                &mdash; mengikuti pilihan ini. Tidak ada pengaturan per kelas, jadi tidak mungkin ada kelas yang berbeda semester sendiri.
            </p>
            <button type="submit" class="shrink-0 px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-sm transition">
                <i class="fas fa-save mr-1.5"></i>Simpan Pengaturan
            </button>
        </div>
    </form>

    <div class="px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-start gap-2">
        <i class="fas fa-triangle-exclamation mt-0.5"></i>
        <span>Bab di aplikasi siswa diatur oleh guru lewat menu Kelola Materi (tiap bab termasuk Semester Ganjil atau Genap). Mengganti semester aktif di halaman ini langsung mengganti bab yang tampil untuk <strong>seluruh siswa di semua kelas</strong>.</span>
    </div>
</div>
@endsection
