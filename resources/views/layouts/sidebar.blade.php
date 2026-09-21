<aside class="w-64 bg-[#13527D] text-white flex flex-col fixed h-screen z-20">
    <div class="p-6 border-b border-white/10">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 bg-white text-[#13527D] rounded-lg flex items-center justify-center font-bold text-lg">BN</div>
            <div>
                <h1 class="font-bold text-sm leading-tight">Binaro</h1>
                <p class="text-[11px] text-white/70">SDN Kalitapen 01</p>
            </div>
        </div>
        <span class="inline-block bg-white/10 text-[10px] font-semibold px-2 py-1 rounded">TAHUN AJARAN 2024/2026</span>
    </div>
    <nav class="flex-1 p-3 space-y-1">
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10"><i class="fas fa-home w-5"></i> Dashboard</a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10"><i class="fas fa-user w-5"></i> Akun Guru</a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10"><i class="fas fa-user-graduate w-5"></i> Akun Siswa</a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10"><i class="fas fa-calendar-alt w-5"></i> Jadwal Mapel</a>
        <a href="{{ route('mapel.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ Route::is('*mapel*') ? 'bg-[#1E5D88] text-white font-semibold' : 'text-white/80 hover:bg-white/10' }}">
            <i class="fas fa-book-open w-5"></i> Kelola Mapel
        </a>
        <a href="{{ route('rpp.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ Route::is('*rpp*') ? 'bg-[#1E5D88] text-white font-semibold' : 'text-white/80 hover:bg-white/10' }}">
            <i class="fas fa-book w-5"></i> Kelola RPP
        </a>
        <a href="{{ route('absensi.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ Route::is('*absensi*') ? 'bg-[#1E5D88] text-white font-semibold' : 'text-white/80 hover:bg-white/10' }}">
            <i class="fas fa-qrcode w-5"></i> Kelola Absensi
        </a>
    </nav>
    <div class="p-4 border-t border-white/10 text-xs">
        <div class="flex items-center gap-2 mb-2 text-emerald-300">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> LMS Online v2.4
        </div>
    </div>
</aside>