@extends('layouts.guru')

@section('header_actions')
    <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-line hover:bg-slate-50 transition print:hidden">
        <i class="fas fa-print text-[11px]"></i> Cetak Jadwal
    </button>
@endsection

@section('content')
<div class="space-y-5 pb-6">
    @include('guru.jadwal._summary')
    @include('guru.jadwal._day_tabs')
    @include('guru.jadwal._schedule_list')
    @include('guru.jadwal._notes')
</div>

@include('guru.jadwal._scripts')
@endsection
