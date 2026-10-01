@extends('layouts.guru')

@section('content')
<div class="p-8 space-y-6 pb-12">
    @include('guru.jadwal._header')
    @include('guru.jadwal._summary')
    @include('guru.jadwal._day-tabs')
    @include('guru.jadwal._schedule-list')
    @include('guru.jadwal._notes')
</div>

@include('guru.jadwal._scripts')
@endsection
