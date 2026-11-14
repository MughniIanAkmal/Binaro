@extends('layouts.siswa')

@section('page_title', 'Profil Saya')

@section('header_actions')
    <a href="{{ route('siswa.dashboard') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-line hover:bg-slate-50 transition">
        <i class="fas fa-arrow-left text-[11px]"></i> Beranda
    </a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    @php
        $namaSiswa = $siswa->nm_siswa ?? session('user_name', 'Siswa');
        $nisn = $siswa->nisn ?? '-';
        $namaKelas = $siswa->kelas->nama_kelas ?? $siswa->kelas->pararel ?? '-';
        $foto = $siswa->foto_profil ?? null;
        $inisial = collect(explode(' ', $namaSiswa))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
        $jk = $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-');
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-line overflow-hidden">
        @include('siswa.profile._identity')

        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            @include('siswa.profile._qr_card')
            @include('siswa.profile._details_card')
        </div>

        @include('siswa.profile._security')
    </div>
</div>

@include('siswa.profile._qr_modal')
@include('siswa.profile._edit_modal')
@include('siswa.profile._scripts')
@endsection