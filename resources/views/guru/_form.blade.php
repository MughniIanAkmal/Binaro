@php $g = $guru ?? null; @endphp

<div class="space-y-4 text-xs">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Guru <span class="text-rose-500">*</span></label>
            <input type="text" name="nama" required value="{{ old('nama', data_get($g, 'nama_guru', data_get($g, 'nama', ''))) }}"
                   placeholder="Contoh: Budi Santoso, S.Pd"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('nama') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">NIP (Nomor Induk Pegawai)</label>
            <input type="text" name="nip" value="{{ old('nip', data_get($g, 'nip', '')) }}"
                   placeholder="Contoh: 19800101001"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('nip') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', data_get($g, 'email', '')) }}"
                   placeholder="Contoh: guru@sekolah.sch.id"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('email') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', data_get($g, 'no_hp', '')) }}"
                   placeholder="Contoh: 081234567890"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('no_hp') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ old('jenis_kelamin', data_get($g, 'jenis_kelamin', '')) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', data_get($g, 'jenis_kelamin', '')) == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error('jenis_kelamin') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Username Login</label>
            <input type="text" name="username" value="{{ old('username', data_get($g, 'username', '')) }}"
                   placeholder="Username untuk login"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('username') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label class="block font-bold text-slate-700 mb-1">Alamat Tempat Tinggal</label>
        <textarea name="alamat" rows="2" placeholder="Alamat lengkap guru..."
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">{{ old('alamat', data_get($g, 'alamat', '')) }}</textarea>
        @error('alamat') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-200">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Password {{ $g ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
            <input type="password" name="password" {{ $g ? '' : 'required' }} placeholder="Minimal 6 karakter"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('password') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" {{ $g ? '' : 'required' }} placeholder="Ketik ulang password"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
        </div>
    </div>
</div>
