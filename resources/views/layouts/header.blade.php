<header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
    <div class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-md">
        Semester Ganjil 2024/2026
    </div>
    <form action="{{ request()->is('rpp*') ? route('rpp.index') : (request()->is('admin/qr-siswa*') ? route('admin.qr.index') : route('absensi.index')) }}" method="GET" class="hidden flex-1 max-w-md mx-8 relative">
        <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
        <input type="text" name="{{ request()->is('admin/qr-siswa*') ? 'q' : 'search' }}" value="{{ request('q', request('search')) }}" placeholder="Cari guru, RPP, mapel, siswa..."
               class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
    </form>
    <div class="flex items-center gap-4">
        <div class="text-right">
            <div class="text-xs font-bold text-slate-800">{{ session('user_name', 'Bpk. Ahmad Fauzi, S.Kom') }}</div>
            <div class="text-[10px] text-slate-500">{{ session('user_type') === 'guru' ? 'Guru Pengajar' : 'Administrator Utama SD' }}</div>
        </div>
        <div class="w-9 h-9 rounded-full bg-[#13527D] text-white flex items-center justify-center font-bold text-xs shadow-sm">
            @php
                $nameParts = explode(' ', trim(session('user_name', 'Ahmad Fauzi')));
                $initials = count($nameParts) >= 2 
                    ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                    : strtoupper(substr($nameParts[0] ?? 'A', 0, 2));
            @endphp
            {{ $initials }}
        </div>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" title="Keluar / Logout" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 text-xs font-semibold transition flex items-center gap-1.5">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</header>
