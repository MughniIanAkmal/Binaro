@extends('layouts.app')

@section('page_title', 'Edit Data Guru')
@section('page_subtitle', 'Perbarui informasi guru ' . ($guru->nama_guru ?? '-') . '.')

@section('breadcrumb_trail')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition shrink-0">Dashboard</a>
    <i class="fas fa-chevron-right text-[8px] opacity-60 shrink-0"></i>
    <a href="{{ route('guru.data.index') }}" class="hover:text-slate-600 transition shrink-0">Data Guru</a>
@endsection

@section('header_actions')
    <a href="{{ route('guru.data.index') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition shadow-xs">
        <i class="fas fa-arrow-left text-slate-400"></i>
        <span>Kembali</span>
    </a>
@endsection

@section('content')
<div class="space-y-6 max-w-3xl">

    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <form action="{{ route('guru.data.update', $guru->id_guru) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('guru.data._form_guru')

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                <a href="{{ route('guru.data.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-bold transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
