<div class="px-6 md:px-8 pb-6 md:pb-8">
    <section class="border border-slate-200 rounded-2xl p-5">
        <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2 mb-1">
            <span class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-sm"><i class="fas fa-key"></i></span>
            Ubah Password
        </h3>
        <p class="text-[11px] text-slate-400 mb-4 ml-10">Minimal 6 karakter. Jangan bagikan ke siapapun.</p>
        <form id="formPassword" action="{{ route('siswa.password.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs ml-0 md:ml-10" novalidate>
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Password Lama</label>
                <input type="password" id="password_lama" name="password_lama" required placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl focus:outline-none focus:bg-white transition @error('password_lama') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#13527D] @enderror">
                <p id="warn_password_lama" class="hidden mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span></span></p>
                @error('password_lama')
                <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                @enderror
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Password Baru</label>
                <input type="password" id="password_baru" name="password" required minlength="6" placeholder="Min. 6 karakter" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl focus:outline-none focus:bg-white transition @error('password') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#13527D] @enderror">
                <p id="warn_password" class="hidden mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span></span></p>
                @error('password')
                <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                @enderror
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Konfirmasi Baru</label>
                <input type="password" id="password_konfirmasi" name="password_confirmation" required minlength="6" placeholder="Ulangi password" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl focus:outline-none focus:bg-white transition @error('password_confirmation') border-rose-400 focus:border-rose-500 @else border-slate-200 focus:border-[#13527D] @enderror">
                <p id="warn_password_confirmation" class="hidden mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span></span></p>
                @error('password_confirmation')
                <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                @enderror
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white font-bold rounded-xl transition">
                    <i class="fas fa-save mr-1.5"></i>Simpan Password
                </button>
            </div>
        </form>
    </section>
</div>

<div class="flex justify-end px-6 md:px-8 pb-6 md:pb-8">
    <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Yakin ingin keluar? Anda akan kembali ke halaman login.')">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-xl transition">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </button>
    </form>
</div>