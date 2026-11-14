@extends(session('user_type') === 'siswa' ? 'layouts.siswa' : (session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app'))

@section('page_title', 'Notifikasi PR')

@section('header_actions')
    @if($belumDibaca > 0)
        <form action="{{ route('siswa.notifikasi_pr.baca_semua') }}" method="POST">
            @csrf
            <button type="submit"
                    onclick="return confirm('Tandai semua {{ $belumDibaca }} notifikasi sebagai sudah dibaca?')"
                    class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition whitespace-nowrap bg-primary"
                    >
                <i class="fas fa-check-double text-[11px]"></i> Tandai Semua Dibaca
            </button>
        </form>
    @endif
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('siswa.notifikasi_pr.index', array_filter(['filter' => 'semua', 'search' => $search])) }}"
           class="bg-white p-4 rounded-2xl border {{ $filter === 'semua' ? 'border-primary ring-2 ring-primary/20 shadow-md' : 'border-line' }} shadow-sm flex items-center justify-between hover:border-primary transition group cursor-pointer block">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $totalNotif }}</div>
                <div class="text-[11px] font-bold text-slate-500 mt-0.5">Total Notifikasi</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-primary flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
                <i class="fas fa-bell"></i>
            </div>
        </a>

        <a href="{{ route('siswa.notifikasi_pr.index', array_filter(['filter' => 'belum_dibaca', 'search' => $search])) }}"
           class="bg-white p-4 rounded-2xl border {{ $filter === 'belum_dibaca' ? 'border-amber-400 ring-2 ring-amber-400/20 shadow-md' : 'border-line' }} shadow-sm flex items-center justify-between hover:border-amber-400 transition group cursor-pointer block"
           title="Klik untuk melihat notifikasi yang belum dibaca">
            <div>
                <div class="text-2xl font-black text-amber-500">{{ $belumDibaca }}</div>
                <div class="text-[11px] font-bold text-slate-500 mt-0.5">Belum Dibaca</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
                <i class="fas fa-envelope"></i>
            </div>
        </a>

        <a href="{{ route('siswa.notifikasi_pr.index', array_filter(['filter' => 'sudah_dibaca', 'search' => $search])) }}"
           class="bg-white p-4 rounded-2xl border {{ $filter === 'sudah_dibaca' ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-md' : 'border-line' }} shadow-sm flex items-center justify-between hover:border-emerald-500 transition group cursor-pointer block"
           title="Klik untuk melihat notifikasi yang sudah dibaca">
            <div>
                <div class="text-2xl font-black text-emerald-600">{{ $sudahDibaca }}</div>
                <div class="text-[11px] font-bold text-slate-500 mt-0.5">Sudah Dibaca</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
                <i class="fas fa-envelope-open"></i>
            </div>
        </a>

        <div class="bg-white p-4 rounded-2xl border border-line shadow-sm flex items-center justify-between">
            <div>
                <div class="text-2xl font-black text-indigo-600">{{ $prAktif }}</div>
                <div class="text-[11px] font-bold text-slate-500 mt-0.5">PR Aktif</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl border border-line p-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            {{-- Tab Filter --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                @foreach([
                    'semua'       => ['Semua', 'fas fa-list', $totalNotif],
                    'belum_dibaca'=> ['Belum Dibaca', 'fas fa-envelope', $belumDibaca],
                    'sudah_dibaca'=> ['Sudah Dibaca', 'fas fa-envelope-open', $sudahDibaca],
                ] as $key => [$label, $icon, $count])
                <a href="{{ route('siswa.notifikasi_pr.index', array_filter(['filter' => $key, 'search' => $search])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $filter === $key ? 'bg-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="{{ $icon }} text-[10px]"></i>
                    <span>{{ $label }}</span>
                    <span class="px-1.5 py-0.5 rounded-full {{ $filter === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} text-[10px] font-extrabold">
                        {{ $count }}
                    </span>
                </a>
                @endforeach
            </div>

            {{-- Search Form --}}
            <form method="GET" action="{{ route('siswa.notifikasi_pr.index') }}" class="relative ml-auto">
                @if($filter !== 'semua')
                    <input type="hidden" name="filter" value="{{ $filter }}">
                @endif
                <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari PR, mapel, atau pesan..."
                       class="pl-8 pr-8 py-1.5 text-xs bg-slate-50 border border-line rounded-lg text-slate-700 focus:outline-none focus:border-primary w-52 sm:w-60">
                @if($search)
                <a href="{{ route('siswa.notifikasi_pr.index', ['filter' => $filter]) }}"
                   class="absolute right-2 top-2 text-slate-400 hover:text-slate-700" title="Hapus pencarian">
                    <i class="fas fa-times text-[10px]"></i>
                </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Daftar Notifikasi --}}
    <div class="space-y-3">
        @forelse($notifikasis as $notif)
        @php
            $pr = $notif->pr;
            $isExpired = $pr ? \Carbon\Carbon::parse($pr->tgl_tenggat)->isPast() : true;
            $tenggatFormatted = $pr ? \Carbon\Carbon::parse($pr->tgl_tenggat)->translatedFormat('d M Y') . ', ' . \Carbon\Carbon::parse($pr->tgl_tenggat)->format('H:i') . ' WIB' : '-';
            $tenggatDiff = $pr ? \Carbon\Carbon::parse($pr->tgl_tenggat)->diffForHumans() : '-';
        @endphp
        <div class="bg-white rounded-2xl border {{ $notif->status_baca ? 'border-line' : 'border-amber-300 shadow-md' }} overflow-hidden transition hover:shadow-md">
            <div class="flex items-start gap-0">
                {{-- Indikator belum dibaca --}}
                <div class="w-1 self-stretch {{ $notif->status_baca ? 'bg-slate-200' : 'bg-amber-400' }} rounded-l-2xl shrink-0"></div>

                <div class="flex-1 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        {{-- Kiri: Info Notifikasi --}}
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                @if(!$notif->status_baca)
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-extrabold px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <i class="fas fa-circle text-[8px]"></i> Baru
                                </span>
                                @endif
                                @if($pr)
                                <span class="bg-sky-100 text-primary text-[10px] font-extrabold px-2 py-0.5 rounded-lg">
                                    {{ $pr->mataPelajaran->nama_mapel ?? 'Mapel' }}
                                </span>
                                @endif
                                @if($pr && !$isExpired)
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-lg flex items-center gap-1">
                                    <i class="fas fa-clock text-[9px]"></i> Aktif · Tenggat {{ $tenggatDiff }}
                                </span>
                                @elseif($pr && $isExpired)
                                <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded-lg flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation text-[9px]"></i> Berakhir {{ $tenggatDiff }}
                                </span>
                                @endif
                            </div>

                            <h3 class="font-extrabold text-sm text-slate-900">
                                {{ $pr->nama_pr ?? 'Tugas PR' }}
                            </h3>

                            <p class="text-[11px] text-slate-600 leading-relaxed line-clamp-2">
                                {{ $notif->pesan }}
                            </p>

                            <div class="flex flex-wrap items-center gap-3 text-[10px] text-slate-400 pt-0.5">
                                @if($pr)
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                    Tenggat: <strong class="text-slate-600 ml-0.5">{{ $tenggatFormatted }}</strong>
                                </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-chalkboard-teacher text-slate-400"></i>
                                    {{ $notif->guru->nama_guru ?? 'Guru' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-clock text-slate-400"></i>
                                    {{ $notif->created_at ? $notif->created_at->translatedFormat('d M Y') . ', ' . $notif->created_at->format('H:i') . ' WIB' : '-' }}
                                </span>
                            </div>
                        </div>

                        {{-- Kanan: Aksi --}}
                        <div class="flex items-center gap-2 shrink-0">
                            @if(!$notif->status_baca)
                            <form action="{{ route('siswa.notifikasi_pr.tandai_baca', $notif->id_notifikasi) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 text-[11px] font-bold rounded-xl transition flex items-center gap-1.5"
                                        title="Tandai sebagai sudah dibaca">
                                    <i class="fas fa-check text-[10px]"></i> Tandai Baca
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('siswa.notifikasi_pr.show', $notif->id_notifikasi) }}"
                               class="px-3 py-1.5 bg-primary hover:bg-primary-hover text-white text-[11px] font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-eye text-[10px]"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-line p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fas fa-bell-slash"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm mb-1">
                @if($filter === 'belum_dibaca')
                    Tidak ada notifikasi yang belum dibaca
                @elseif($filter === 'sudah_dibaca')
                    Belum ada notifikasi yang sudah dibaca
                @elseif($search)
                    Tidak ditemukan notifikasi untuk "{{ $search }}"
                @else
                    Belum ada notifikasi PR
                @endif
            </h3>
            <p class="text-[11px] text-slate-400">
                @if($filter !== 'semua')
                    <a href="{{ route('siswa.notifikasi_pr.index') }}" class="text-primary hover:underline font-medium">Lihat semua notifikasi</a>
                @else
                    Guru belum mengirimkan notifikasi tugas PR kepada kamu.
                @endif
            </p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifikasis->hasPages())
    <div class="flex justify-center">
        {{ $notifikasis->links() }}
    </div>
    @endif

</div>
@endsection
