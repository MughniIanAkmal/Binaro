@extends('layouts.siswa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Profil Siswa</h1>
        <p class="text-xs text-slate-500">Identitas, QR absensi, biodata, dan keamanan akun</p>
    </div>

    @if(session('success'))
    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check text-emerald-500 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation text-rose-500 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
        $namaSiswa = $siswa->nm_siswa ?? session('user_name', 'Siswa');
        $nisn = $siswa->nisn ?? '-';
        $namaKelas = $siswa->kelas->nama_kelas ?? $siswa->kelas->pararel ?? '-';
        $foto = $siswa->foto_profil ?? null;
        $inisial = collect(explode(' ', $namaSiswa))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
        $jk = $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-');
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Banner -->
        <div class="bg-[#13527D] h-28 relative overflow-hidden">
            <div class="absolute top-0 right-0 opacity-10 translate-x-6 -translate-y-6">
                <i class="fas fa-graduation-cap text-8xl"></i>
            </div>
        </div>

        <!-- Foto + Nama + NISN + Kelas -->
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

        <!-- Isi: QR + Keterangan -->
        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- QR langsung tampil -->
            <section class="border border-slate-200 rounded-2xl p-5 bg-slate-50/50">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 bg-[#13527D]/10 text-[#13527D] rounded-lg flex items-center justify-center text-sm"><i class="fas fa-qrcode"></i></span>
                    QR Siswa
                </h3>
                <div class="bg-white border border-dashed border-slate-300 rounded-xl py-5 flex justify-center">
                    @if(!empty($qrSvg))
                    <img id="qrImage" src="{{ $qrSvg }}" alt="QR {{ $namaSiswa }}" class="w-48 h-48">
                    @else
                    <p class="text-xs text-slate-400 py-10">QR belum tersedia.</p>
                    @endif
                </div>
                <p class="text-center text-[11px] text-slate-400 mt-3">Kode unik</p>
                <p class="text-center font-mono font-black tracking-widest text-slate-900">{{ $siswa->barcode->kode_barcode ?? '-' }}</p>
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <button type="button" onclick="toggleModal('modal-qr')" class="py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl transition">
                        <i class="fas fa-expand mr-1.5"></i>Perbesar
                    </button>
                    <button type="button" onclick="cetakQR()" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition">
                        <i class="fas fa-print mr-1.5"></i>Cetak
                    </button>
                </div>
            </section>

            <!-- Keterangan langsung tampil + tombol Edit -->
            <section class="border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-sm"><i class="fas fa-address-card"></i></span>
                        Keterangan
                    </h3>
                    <button type="button" onclick="toggleModal('modal-edit')" class="px-3.5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-[11px] font-bold rounded-lg transition">
                        <i class="fas fa-pen mr-1.5"></i>Edit
                    </button>
                </div>
                <dl class="text-xs divide-y divide-slate-100">
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">Nama</dt><dd class="font-bold text-slate-900 text-right">{{ $namaSiswa }}</dd></div>
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">NISN</dt><dd class="font-bold font-mono text-slate-900">{{ $nisn }}</dd></div>
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">Kelas</dt><dd class="font-bold text-slate-900">{{ $namaKelas }}</dd></div>
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">Username</dt><dd class="font-bold text-slate-900">{{ $siswa->username ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">Email</dt><dd class="font-bold text-slate-900 break-all text-right">{{ $siswa->email ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">No. HP</dt><dd class="font-bold text-slate-900">{{ $siswa->no_hp ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">Jenis Kelamin</dt><dd class="font-bold text-slate-900">{{ $jk }}</dd></div>
                    <div class="flex justify-between gap-4 py-2.5"><dt class="text-slate-500 font-semibold">Alamat</dt><dd class="font-bold text-slate-900 text-right max-w-[60%]">{{ $siswa->alamat ?? '-' }}</dd></div>
                </dl>
            </section>
        </div>

        <!-- Ubah password langsung tampil -->
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

        <!-- Tombol keluar pojok kanan bawah -->
        <div class="flex justify-end px-6 md:px-8 pb-6 md:pb-8">
            <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Yakin ingin keluar? Anda akan kembali ke halaman login.')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-xl transition">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal perbesar QR -->
<div id="modal-qr" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full overflow-hidden">
        <div class="bg-[#13527D] px-5 py-4 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm"><i class="fas fa-qrcode mr-2"></i>QR Siswa</h3>
            <button type="button" onclick="toggleModal('modal-qr')" class="text-white/70 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 text-center">
            <p class="font-bold text-slate-900">{{ $namaSiswa }}</p>
            <p class="text-[11px] text-slate-500 mb-4">{{ $namaKelas }} &bull; {{ $nisn }}</p>
            @if(!empty($qrSvg))
            <img src="{{ $qrSvg }}" alt="QR besar" class="w-64 h-64 mx-auto">
            @endif
            <p class="font-mono font-black tracking-widest mt-3">{{ $siswa->barcode->kode_barcode ?? '-' }}</p>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button type="button" onclick="toggleModal('modal-qr')" class="py-2.5 bg-slate-100 text-xs font-bold rounded-xl">Tutup</button>
                <button type="button" onclick="cetakQR()" class="py-2.5 bg-[#13527D] text-white text-xs font-bold rounded-xl">Cetak</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal edit keterangan -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden">
        <div class="bg-[#13527D] px-5 py-4 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm"><i class="fas fa-pen mr-2"></i>Edit Keterangan</h3>
            <button type="button" onclick="toggleModal('modal-edit')" class="text-white/70 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form id="formEdit" action="{{ route('siswa.profile.update') }}" method="POST" class="p-5 space-y-3 text-xs" novalidate>
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap <span class="font-normal text-slate-400">(hubungi admin untuk mengubah)</span></label>
                    <input type="text" value="{{ $namaSiswa }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">NISN</label>
                    <input type="text" value="{{ $nisn }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kelas</label>
                    <input type="text" value="{{ $namaKelas }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-500">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Username <span class="font-normal text-slate-400">(huruf + spasi, maks. 25)</span></label>
                <input type="text" id="edit_username" name="username" required maxlength="25" pattern="[A-Za-z ]+" title="Hanya huruf A-Z dan spasi, maksimal 25" value="{{ old('username', $siswa->username) }}" oninput="this.value=this.value.replace(/[^A-Za-z ]/g,'').slice(0,25)" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl focus:outline-none focus:bg-white transition @error('username') border-rose-400 @else border-slate-200 focus:border-[#13527D] @enderror">
                <p class="text-[10px] text-slate-400 mt-1"><span id="count_username">0</span>/25 huruf dan spasi.</p>
                @error('username')
                <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email <span class="font-normal text-slate-400">(wajib ada @)</span></label>
                    <input type="email" id="edit_email" name="email" required maxlength="100" value="{{ old('email', $siswa->email) }}" oninput="cekEmailLive(this)" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl focus:outline-none focus:bg-white transition @error('email') border-rose-400 @else border-slate-200 focus:border-[#13527D] @enderror">
                    <p id="warn_email" class="hidden mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span>Email harus mengandung tanda @.</span></p>
                    @error('email')
                    <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. HP <span class="font-normal text-slate-400">(angka, maks. 12)</span></label>
                    <input type="text" id="edit_nohp" name="no_hp" inputmode="numeric" maxlength="12" pattern="[0-9]*" title="Hanya angka, maksimal 12" value="{{ old('no_hp', $siswa->no_hp) }}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,12)" class="w-full px-3.5 py-2.5 bg-slate-50 border rounded-xl focus:outline-none focus:bg-white transition @error('no_hp') border-rose-400 @else border-slate-200 focus:border-[#13527D] @enderror">
                    <p class="text-[10px] text-slate-400 mt-1"><span id="count_nohp">0</span>/12 angka saja.</p>
                    @error('no_hp')
                    <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] transition">
                    <option value="">— Pilih —</option>
                    <option value="L" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) === 'P')>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat</label>
                <textarea name="alamat" rows="2" maxlength="500" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] transition">{{ old('alamat', $siswa->alamat) }}</textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal('modal-edit')" class="flex-1 py-2.5 bg-slate-100 font-bold rounded-xl">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-[#13527D] text-white font-bold rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.contains('hidden') ? (el.classList.remove('hidden'), el.classList.add('flex')) : (el.classList.add('hidden'), el.classList.remove('flex'));
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['modal-qr', 'modal-edit'].forEach(id => {
        const m = document.getElementById(id);
        if (m && !m.classList.contains('hidden')) toggleModal(id);
    });
});
function cetakQR() {
    const img = document.getElementById('qrImage');
    if (!img) { alert('QR belum tersedia. Hubungi admin / guru.'); return; }
    const kode = @json($siswa->barcode->kode_barcode ?? '-');
    const nama = @json($namaSiswa);
    const w = window.open('', '_blank', 'width=480,height=640');
    if (!w) { alert('Popup diblokir browser. Izinkan popup untuk situs ini lalu coba lagi.'); return; }
    w.document.write('<html><head><title>Cetak QR - ' + nama + '</title><style>body{font-family:sans-serif;text-align:center;padding:32px}img{width:320px;height:320px}h2{margin:0}p{color:#555}.kode{font-weight:900;letter-spacing:.2em;font-size:20px}</style></head><body><h2>' + nama + '</h2><p>{{ $namaKelas }} &bull; {{ $nisn }}</p><img src="' + img.src + '"><p class="kode">' + kode + '</p><script>window.onload=function(){window.print()}<\/script></body></html>');
    w.document.close();
}
// Peringatan langsung saat mengetik: 1) password lama kosong, 2) <6 karakter, 3) konfirmasi tidak sesuai
(function () {
    const lama = document.getElementById('password_lama');
    const baru = document.getElementById('password_baru');
    const konf = document.getElementById('password_konfirmasi');
    const form = document.getElementById('formPassword');
    if (!lama || !baru || !konf || !form) return;

    function showWarn(input, boxId, msg) {
        const box = document.getElementById(boxId);
        if (!box) return;
        const text = box.querySelector('span');
        if (msg) {
            box.classList.remove('hidden');
            if (text) text.textContent = msg;
            input.classList.add('border-rose-400');
            input.classList.remove('border-slate-200');
        } else {
            box.classList.add('hidden');
            input.classList.remove('border-rose-400');
            input.classList.add('border-slate-200');
        }
    }

    function validateLive() {
        // Aturan 3: minimal 6 karakter (hanya tampil jika sudah diisi)
        showWarn(baru, 'warn_password', baru.value !== '' && baru.value.length < 6 ? 'Password baru minimal 6 karakter.' : '');
        // Aturan 2: konfirmasi harus sama (hanya tampil jika keduanya sudah diisi)
        showWarn(konf, 'warn_password_confirmation', konf.value !== '' && baru.value !== '' && konf.value !== baru.value ? 'Konfirmasi password baru tidak sesuai.' : '');
        // Aturan 1 (client): password lama wajib diisi — ditandai saat submit kosong
        if (document.activeElement === lama || lama.value !== '') {
            showWarn(lama, 'warn_password_lama', lama.value === '' ? 'Password lama wajib diisi.' : '');
        }
    }

    [lama, baru, konf].forEach(el => el.addEventListener('input', validateLive));

    form.addEventListener('submit', function (e) {
        let batal = false;
        if (lama.value.trim() === '') { showWarn(lama, 'warn_password_lama', 'Password lama wajib diisi. Diisi dulu password Anda saat ini.'); batal = true; }
        if (baru.value.length < 6) { showWarn(baru, 'warn_password', 'Password baru minimal 6 karakter.'); batal = true; }
        if (konf.value !== baru.value) { showWarn(konf, 'warn_password_confirmation', 'Konfirmasi password baru tidak sesuai. Samakan dengan password baru.'); batal = true; }
        if (batal) { e.preventDefault(); lama.classList.contains('border-rose-400') ? lama.focus() : (baru.classList.contains('border-rose-400') ? baru.focus() : konf.focus()); }
    });
})();
function cekEmailLive(input) {
    const box = document.getElementById('warn_email');
    if (!box) return;
    if (input.value !== '' && !input.value.includes('@')) {
        box.classList.remove('hidden');
        input.classList.add('border-rose-400');
    } else {
        box.classList.add('hidden');
        input.classList.remove('border-rose-400');
    }
}
// Batasan & validasi form Edit Keterangan: username huruf maks 25, no HP angka maks 12, email wajib @
(function () {
    const u = document.getElementById('edit_username');
    const hp = document.getElementById('edit_nohp');
    const em = document.getElementById('edit_email');
    const form = document.getElementById('formEdit');
    const cu = document.getElementById('count_username');
    const ch = document.getElementById('count_nohp');
    function refreshCount() {
        if (u && cu) cu.textContent = u.value.length;
        if (hp && ch) ch.textContent = hp.value.length;
    }
    if (u) u.addEventListener('input', refreshCount);
    if (hp) hp.addEventListener('input', refreshCount);
    refreshCount();
    if (!form) return;
    form.addEventListener('submit', function (e) {
        let msg = '';
        if (u && !/^(?=.*[A-Za-z])[A-Za-z ]+$/.test(u.value)) msg = 'Username hanya boleh berisi huruf (A-Z) dan spasi, maksimal 25.';
        else if (u && u.value.length > 25) msg = 'Username maksimal 25 huruf.';
        else if (em && !em.value.includes('@')) msg = 'Email harus mengandung tanda @, contoh: nama@email.com.';
        else if (hp && hp.value !== '' && (!/^[0-9]+$/.test(hp.value) || hp.value.length > 12)) msg = 'No. HP hanya angka dan maksimal 12.';
        if (msg) { e.preventDefault(); alert(msg); }
    });
})();
@if(session('open_edit') || $errors->has('username') || $errors->has('email') || $errors->has('no_hp'))
toggleModal('modal-edit');
@endif
</script>
@endsection
