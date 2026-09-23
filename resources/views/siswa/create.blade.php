@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6 max-w-3xl">
    <div class="flex justify-between items-start">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1 text-slate-400">
                <a href="{{ route('siswa.index') }}" class="hover:underline">Data Siswa</a>
                <span>&gt; Tambah Siswa Baru</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Data Siswa</h2>
            <p class="text-xs text-slate-500">Lengkapi informasi berikut. Kode QR barcode akan dibuat secara otomatis.</p>
        </div>
        <a href="{{ route('siswa.index') }}" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <form action="{{ route('siswa.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('siswa._form')

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                <a href="{{ route('siswa.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-lg text-xs font-bold transition shadow-sm">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
