@extends('layouts.siswa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Profil Siswa</h1>
        <p class="text-xs text-slate-500">Identitas, QR absensi, biodata, dan keamanan akun</p>
    </div>

    @include('siswa.profile._alerts')

    @php
        $namaSiswa = $siswa->nm_siswa ?? session('user_name', 'Siswa');
        $nisn = $siswa->nisn ?? '-';
        $namaKelas = $siswa->kelas->nama_kelas ?? $siswa->kelas->pararel ?? '-';
        $foto = $siswa->foto_profil ?? null;
        $inisial = collect(explode(' ', $namaSiswa))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
        $jk = $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-');
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @include('siswa.profile._identity')

        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            @include('siswa.profile._qr-card')
            @include('siswa.profile._details-card')
        </div>

        @include('siswa.profile._security')
    </div>
</div>

@include('siswa.profile._qr-modal')
@include('siswa.profile._edit-modal')
@include('siswa.profile._scripts')
@endsection