@extends('layouts.app')

@section('page_title', 'QR & Absensi')
@section('page_subtitle', 'Satu halaman untuk kartu QR siswa dan kehadiran hari ini — scan, buat QR, dan rekap tanpa pindah menu.')

@section('breadcrumb_trail')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition shrink-0">Dashboard</a>
@endsection

@section('header_actions')
    <form action="{{ route('absensi.scan-qr') }}" method="POST" class="flex gap-2">
        @csrf
        <input type="text" name="kode_barcode" placeholder="Scan Barcode / Input ID..." required
               class="px-4 h-10 text-[12px] font-bold text-slate-700 border border-line rounded-xl bg-white focus:outline-none focus:border-primary">
        <button type="submit" class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition">
            <i class="fas fa-qrcode text-[11px]"></i> Scan QR
        </button>
    </form>
    <a href="{{ route('admin.absensi.settings') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary">
        <i class="fas fa-clock text-[11px]"></i> Pengaturan Jam Absen
    </a>
@endsection

@section('content')
<div class="space-y-5">

    {{-- KPI: absensi hari ini + progres QR --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-2xl font-black text-slate-900">{{ $kpi['persentase'] }}%</div>
            <div class="text-xs text-slate-600 font-semibold mt-1">Tingkat Kehadiran ({{ $selectedDate }})</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $kpi['hadir'] }} hadir / {{ $kpi['total'] }} siswa</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-2xl font-black text-slate-900">{{ $kpi['hadir'] }} <span class="text-sm font-semibold text-slate-400">/ {{ $kpi['total'] }}</span></div>
            <div class="text-xs text-slate-600 font-semibold mt-1">Total Siswa Hadir</div>
            <div class="text-[11px] text-amber-600 font-bold mt-1">{{ $kpi['izin_sakit'] }} izin / sakit</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-2xl font-black text-rose-600">{{ $kpi['alpa'] }} <span class="text-sm font-semibold text-slate-400">Siswa</span></div>
            <div class="text-xs text-slate-600 font-semibold mt-1">Tanpa Keterangan (Alpa)</div>
            <div class="text-[11px] text-rose-500 font-bold mt-1">Perlu konfirmasi wali</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-2xl font-black text-slate-900">{{ $kpi['sudah_qr'] }} <span class="text-sm font-semibold text-slate-400">/ {{ $kpi['total'] }}</span></div>
            <div class="text-xs text-slate-600 font-semibold mt-1">Siswa Sudah Punya QR</div>
            <div class="text-[11px] text-slate-400 mt-1">
                @if($kpi['sudah_qr'] < $kpi['total'])
                    <form method="POST" action="{{ route('admin.qr.storeAll') }}" class="inline">
                        @csrf
                        <button class="text-primary hover:underline font-bold">Buat semua yang belum punya</button>
                    </form>
                @else
                    Semua siswa sudah punya QR.
                @endif
            </div>
        </div>
    </div>

    {{-- Filter + tabel gabungan --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-3">
            <form action="{{ route('admin.qr_absensi.index') }}" method="GET" class="flex gap-2 items-center flex-wrap">
                <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                <select name="id_rooms" onchange="this.form.submit()" class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id_rooms }}" {{ (string) $selectedKelas === (string) $kelas->id_rooms ? 'selected' : '' }}>Kelas {{ $kelas->nama_kelas }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="Hadir" {{ request('status') === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="izin_sakit" {{ request('status') === 'izin_sakit' ? 'selected' : '' }}>Izin / Sakit</option>
                    <option value="Alpa" {{ request('status') === 'Alpa' ? 'selected' : '' }}>Alpa</option>
                    <option value="belum_qr" {{ request('status') === 'belum_qr' ? 'selected' : '' }}>Belum punya QR</option>
                </select>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NISN"
                       class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 focus:outline-none focus:border-primary">
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('admin.qr.cetakKelas') }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Cetak Semua Kartu</a>
                <a href="{{ route('admin.absensi.rekap') }}" class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:opacity-90">Rekap Absensi</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">Siswa</th>
                        <th class="p-3.5">Kelas</th>
                        <th class="p-3.5">QR Code</th>
                        <th class="p-3.5">Status {{ $selectedDate }}</th>
                        <th class="p-3.5">Waktu</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($siswaList as $s)
                        @php
                            $status = $s->id_absen ? $s->status_kehadiran : 'Alpa';
                            $badge = match($status) {
                                'Hadir' => 'bg-emerald-100 text-emerald-700',
                                'Izin', 'Sakit' => 'bg-amber-100 text-amber-700',
                                default => 'bg-rose-100 text-rose-700',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 pl-6">
                                <div class="font-bold text-slate-900">{{ $s->nm_siswa }}</div>
                                <div class="text-[11px] text-slate-400">NISN: {{ $s->nisn }}</div>
                            </td>
                            <td class="p-3.5">{{ $s->nama_kelas ?? '-' }}</td>
                            <td class="p-3.5">
                                @if($s->barcode)
                                    <span class="font-mono font-bold text-slate-700">{{ $s->barcode->kode }}</span>
                                @else
                                    <span class="text-slate-400 italic">Belum punya QR</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $badge }}">{{ $status }}</span>
                                @if($s->keterangan)
                                    <div class="text-[11px] text-slate-400 mt-1">{{ $s->keterangan }}</div>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-600">{{ $s->waktu_absen ? \Carbon\Carbon::parse($s->waktu_absen)->format('H:i') : '-' }}</td>
                            <td class="p-3.5 text-center">
                                <div class="inline-flex gap-1.5 flex-wrap justify-center">
                                    @if($s->barcode)
                                        <form method="POST" action="{{ route('admin.qr.regenerate', $s->id_siswa) }}" class="inline" onsubmit="return confirm('Buat ulang QR siswa ini? Kode lama tidak bisa dipakai lagi.')">
                                            @csrf
                                            <button class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded text-xs font-semibold">Buat Ulang</button>
                                        </form>
                                        <a href="{{ route('admin.qr.cetak', $s->id_siswa) }}" target="_blank" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-800 text-white rounded text-xs font-semibold">Cetak</a>
                                    @else
                                        <form method="POST" action="{{ route('admin.qr.store', $s->id_siswa) }}" class="inline">
                                            @csrf
                                            <button class="px-2.5 py-1 bg-primary hover:opacity-90 text-white rounded text-xs font-semibold">Buat QR</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('absensi.index', ['tanggal' => $selectedDate, 'id_rooms' => $selectedKelas]) }}" class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-semibold">Ubah Absen</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-slate-400 text-xs">Tidak ada siswa yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $siswaList->links() }}
        </div>
    </div>
</div>
@endsection
