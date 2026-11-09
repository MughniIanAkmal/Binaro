@extends('layouts.guru')

@section('page_title', 'Scan Absen Siswa')
@section('page_subtitle', 'Arahkan QR kartu siswa ke kamera. Jika QR tidak terbaca, ketik kodenya secara manual.')

@section('header_actions')
    <a href="{{ route('guru.absensi.rekap') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-[#E6EBF2] hover:bg-slate-50 transition">
        <i class="fas fa-chart-pie text-[11px]"></i> Rekap Absensi
    </a>
@endsection

@section('content')
<style>
    /* Orientasi preview diatur dinamis via JS (fungsi terapkanCermin):
       kamera depan dicerminkan seperti aplikasi selfie, kamera belakang tampil apa adanya */
    #reader video { max-width: 100%; }
    /* Overlay efek scan di atas preview kamera */
    .scan-overlay { position: absolute; inset: 0; pointer-events: none; z-index: 20; }
    .scan-overlay .corner { position: absolute; width: 26px; height: 26px; border: 3px solid #22c55e; }
    .scan-overlay .corner.tl { top: 12px; left: 12px; border-right: none; border-bottom: none; border-top-left-radius: 8px; }
    .scan-overlay .corner.tr { top: 12px; right: 12px; border-left: none; border-bottom: none; border-top-right-radius: 8px; }
    .scan-overlay .corner.bl { bottom: 12px; left: 12px; border-right: none; border-top: none; border-bottom-left-radius: 8px; }
    .scan-overlay .corner.br { bottom: 12px; right: 12px; border-left: none; border-top: none; border-bottom-right-radius: 8px; }
    .scan-laser {
        position: absolute; left: 16px; right: 16px; height: 2px; border-radius: 2px;
        background: linear-gradient(90deg, transparent, #22c55e, transparent);
        box-shadow: 0 0 10px rgba(34, 197, 94, .9);
        animation: scanLaser 2.2s ease-in-out infinite;
    }
    @keyframes scanLaser { 0%, 100% { top: 16px; } 50% { top: calc(100% - 18px); } }
</style>

<div class="space-y-5 max-w-3xl mx-auto">
    @include('guru.absen._summary')
    @include('guru.absen._camera')
    @include('guru.absen._forms')
    @include('guru.absen._attendance_list')
</div>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
@include('guru.absen._scan_script')
@include('guru.absen._permission_script')
</script>
@endsection