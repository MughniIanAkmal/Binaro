<div class="bg-[#13527D] h-28 relative overflow-hidden">
    <div class="absolute top-0 right-0 opacity-10 translate-x-6 -translate-y-6">
        <i class="fas fa-graduation-cap text-8xl"></i>
    </div>
</div>

<div class="text-center px-6 -mt-12 relative z-10">
    <div class="mx-auto w-24 h-24 rounded-full p-1 bg-white shadow-md relative">
        @if($foto)
        <img src="{{ str_starts_with($foto, 'http') ? $foto : asset('storage/' . $foto) }}" alt="Foto {{ $namaSiswa }}" class="w-full h-full rounded-full object-cover">
        @else
        <div class="w-full h-full rounded-full bg-[#13527D] text-white flex items-center justify-center text-2xl font-black">{{ $inisial ?: 'S' }}</div>
        @endif
    </div>
    <h2 class="mt-3 text-lg font-black text-slate-900">{{ $namaSiswa }}</h2>
    <p class="text-xs text-slate-500 mt-1">NISN <span class="font-bold font-mono text-slate-700">{{ $nisn }}</span></p>
    <p class="text-xs text-slate-500">Kelas <span class="font-bold text-slate-700">{{ $namaKelas }}</span></p>
</div>

<div class="border-t border-slate-100 mt-5"></div>