@php $s = $siswa ?? null; @endphp

<div class="space-y-4 text-xs">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
            <input type="text" name="nama" required value="{{ old('nama', data_get($s, 'nama_siswa', data_get($s, 'nm_siswa', ''))) }}"
                   placeholder="Contoh: Aditya Pratama"
                     oninput="this.value=this.value.replace(/[^\p{L} ]/gu,'')"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('nama') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">NISN / NIS</label>
            <input type="text" name="nis" value="{{ old('nis', data_get($s, 'nisn', data_get($s, 'nis', '')) ) }}"
                   placeholder="Contoh: 0012345601"
                   inputmode="numeric" pattern="[0-9]{0,12}" maxlength="12" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('nis') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Kelas / Rombel</label>
            <select name="id_rooms" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id_rooms }}" {{ old('id_rooms', data_get($s, 'id_rooms')) == $k->id_rooms ? 'selected' : '' }}>
                        {{ $k->pararel }}
                    </option>
                @endforeach
            </select>
            @error('id_rooms') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Mata Pelajaran Utama (Opsional)</label>
            <select name="id_mapel" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($mapel as $m)
                    <option value="{{ $m->id_mapel }}" {{ old('id_mapel', data_get($s, 'id_mapel')) == $m->id_mapel ? 'selected' : '' }}>
                        {{ $m->nama_mapel }}
                    </option>
                @endforeach
            </select>
            @error('id_mapel') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Email Siswa / Wali</label>
            <input type="email" name="email" value="{{ old('email', data_get($s, 'email', '')) }}"
                   placeholder="Contoh: siswa@sekolah.sch.id"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('email') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor HP / WhatsApp Wali</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', data_get($s, 'no_hp', '')) }}"
                   placeholder="Contoh: 081234567890"
                                     inputmode="numeric" pattern="[0-9]{0,12}" maxlength="12" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('no_hp') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ old('jenis_kelamin', data_get($s, 'jenis_kelamin', '')) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', data_get($s, 'jenis_kelamin', '')) == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error('jenis_kelamin') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Username Login</label>
            <input type="text" name="username" value="{{ old('username', data_get($s, 'username', '')) }}"
                   placeholder="Username login siswa"
                     oninput="this.value=this.value.replace(/[^A-Za-z0-9]/g,'')"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('username') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label class="block font-bold text-slate-700 mb-1">Alamat Rumah</label>
        <textarea name="alamat" rows="2" placeholder="Alamat lengkap tempat tinggal siswa..."
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">{{ old('alamat', data_get($s, 'alamat', '')) }}</textarea>
        @error('alamat') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-200">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Password {{ $s ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
            <input type="password" name="password" {{ $s ? '' : 'required' }} placeholder="Minimal 6 karakter"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
            @error('password') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" {{ $s ? '' : 'required' }} placeholder="Ketik ulang password"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D] bg-white">
        </div>
    </div>
</div>
