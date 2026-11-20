@php
    $s = $siswa ?? null;
    // Di halaman daftar, satu modal edit dirender untuk setiap baris. Hanya modal
    // milik data yang gagal divalidasi yang boleh memakai old input dan pesan error,
    // agar nilai dari baris lain tidak bocor ke modal yang tidak terkait.
    $pakaiOld = $pakaiOld ?? true;
    $nilaiLama = fn ($key, $default = null) => $pakaiOld ? old($key, $default) : $default;
@endphp

<div class="space-y-4 text-xs">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
            <input type="text" name="nama" required maxlength="100" value="{{ $nilaiLama('nama', data_get($s, 'nama_siswa', data_get($s, 'nm_siswa', '')) ) }}"
                   placeholder="Contoh: Aditya Pratama"
                   oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')"
                   class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
            <p class="text-[10px] text-slate-400 mt-1">Hanya huruf dan spasi, tanpa angka atau simbol.</p>
            @if($pakaiOld)
                @error('nama') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">NISN / NIS <span class="text-rose-500">*</span></label>
            <input type="text" name="nis" required value="{{ $nilaiLama('nis', data_get($s, 'nisn', data_get($s, 'nis', '' )) ) }}"
                   placeholder="Contoh: 0012345601"
                   inputmode="numeric" pattern="[0-9]{0,15}" maxlength="15" oninput="this.value=this.value.replace(/\D/g,'').slice(0,15)"
                   class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
            <p class="text-[10px] text-slate-400 mt-1">Hanya angka, maksimal 15 digit.</p>
            @if($pakaiOld)
                @error('nis') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Kelas / Rombel</label>
            <select name="id_rooms" class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id_rooms }}" {{ $nilaiLama('id_rooms', data_get($s, 'id_rooms')) == $k->id_rooms ? 'selected' : '' }}>
                        {{ $k->pararel }}
                    </option>
                @endforeach
            </select>
            @if($pakaiOld)
                @error('id_rooms') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Email Siswa / Wali <span class="text-rose-500">*</span></label>
            <input type="email" name="email" required value="{{ $nilaiLama('email', data_get($s, 'email', '')) }}"
                   placeholder="Contoh: siswa@sekolah.sch.id"
                   class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
            <p class="text-[10px] text-slate-400 mt-1">Wajib mengandung tanda @, contoh: nama@email.com.</p>
            @if($pakaiOld)
                @error('email') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor HP / WhatsApp Wali</label>
            <input type="text" name="no_hp" value="{{ $nilaiLama('no_hp', data_get($s, 'no_hp', '')) }}"
                   placeholder="Contoh: 081234567890"
                   inputmode="numeric" pattern="[0-9]{0,12}" maxlength="12" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)"
                   class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
            <p class="text-[10px] text-slate-400 mt-1">Hanya angka, maksimal 12 digit.</p>
            @if($pakaiOld)
                @error('no_hp') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ $nilaiLama('jenis_kelamin', data_get($s, 'jenis_kelamin', '')) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $nilaiLama('jenis_kelamin', data_get($s, 'jenis_kelamin', '')) == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @if($pakaiOld)
                @error('jenis_kelamin') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Username Login <span class="text-rose-500">*</span></label>
            <input type="text" name="username" required maxlength="50" value="{{ $nilaiLama('username', data_get($s, 'username', '')) }}"
                   placeholder="Username login siswa"
                   oninput="this.value=this.value.replace(/[^A-Za-z0-9._]/g,'')"
                   class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
            @if($pakaiOld)
                @error('username') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>

    <div>
        <label class="block font-bold text-slate-700 mb-1">Alamat Rumah</label>
        <textarea name="alamat" rows="2" maxlength="500" placeholder="Alamat lengkap tempat tinggal siswa (maksimal 500 karakter)..."
                  class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">{{ $nilaiLama('alamat', data_get($s, 'alamat', '')) }}</textarea>
        @if($pakaiOld)
            @error('alamat') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-line">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Password {{ $s ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
            <input type="password" name="password" {{ $s ? '' : 'required' }} placeholder="Minimal 6 karakter"
                   class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
            @if($pakaiOld)
                @error('password') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" {{ $s ? '' : 'required' }} placeholder="Ketik ulang password"
                   class="w-full px-3 py-2 border border-line rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 bg-white transition">
        </div>
    </div>
</div>
