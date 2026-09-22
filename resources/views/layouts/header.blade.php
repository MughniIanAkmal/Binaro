<header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
    <div class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-md">
        Semester Ganjil 2024/2026
    </div>
    <form action="{{ request()->is('rpp*') ? route('rpp.index') : (request()->is('admin/qr-siswa*') ? route('admin.qr.index') : route('absensi.index')) }}" method="GET" class="flex-1 max-w-md mx-8 relative">
        <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
        <input type="text" name="{{ request()->is('admin/qr-siswa*') ? 'q' : 'search' }}" value="{{ request('q', request('search')) }}" placeholder="Cari guru, RPP, mapel, siswa..."
               class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
    </form>
    <div class="flex items-center gap-3">
        <div class="text-right">
            <div class="text-xs font-bold">Bpk. Ahmad Fauzi, S.Kom</div>
            <div class="text-[10px] text-slate-500">Administrator Utama SD</div>
        </div>
        <div class="w-9 h-9 rounded-full bg-[#13527D] text-white flex items-center justify-center font-bold text-xs">AF</div>
    </div>
</header>
