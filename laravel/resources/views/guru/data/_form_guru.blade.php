@php
    $g = $guru ?? null;
    // Di halaman daftar, satu modal edit dirender untuk setiap baris. Hanya modal
    // milik data yang gagal divalidasi yang boleh memakai old input dan pesan error,
    // agar nilai dari baris lain tidak bocor ke modal yang tidak terkait.
    $pakaiOld = $pakaiOld ?? true;
    $nilaiLama = fn ($key, $default = null) => $pakaiOld ? old($key, $default) : $default;
@endphp

<div class="space-y-4 text-xs">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Guru <span class="text-rose-500">*</span></label>
            <input type="text" name="nama" required maxlength="100" value="{{ $nilaiLama('nama', data_get($g, 'nama_guru', data_get($g, 'nama', ''))) }}"
                   placeholder="Contoh: Budi Santoso"
                   oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
            <p class="text-[10px] text-slate-400 mt-1">Hanya huruf dan spasi, tanpa angka atau simbol.</p>
            @if($pakaiOld)
                @error('nama') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">NIP (Nomor Induk Pegawai)</label>
            <input type="text" name="nip" value="{{ $nilaiLama('nip', data_get($g, 'nip', '')) }}"
                   placeholder="Contoh: 19800101001"
                   inputmode="numeric" pattern="[0-9]{0,15}" maxlength="15" oninput="this.value=this.value.replace(/\D/g,'').slice(0,15)"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
            <p class="text-[10px] text-slate-400 mt-1">Hanya angka, maksimal 15 digit.</p>
            @if($pakaiOld)
                @error('nip') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
            <input type="email" name="email" required maxlength="100" value="{{ $nilaiLama('email', data_get($g, 'email', '')) }}"
                   placeholder="Contoh: guru@sekolah.sch.id"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
            <p class="text-[10px] text-slate-400 mt-1">Wajib mengandung tanda @, contoh: nama@email.com.</p>
            @if($pakaiOld)
                @error('email') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
            <input type="text" name="no_hp" value="{{ $nilaiLama('no_hp', data_get($g, 'no_hp', '')) }}"
                   placeholder="Contoh: 081234567890"
                   inputmode="numeric" pattern="[0-9]{0,12}" maxlength="12" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
            <p class="text-[10px] text-slate-400 mt-1">Hanya angka, maksimal 12 digit.</p>
            @if($pakaiOld)
                @error('no_hp') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ $nilaiLama('jenis_kelamin', data_get($g, 'jenis_kelamin', '')) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $nilaiLama('jenis_kelamin', data_get($g, 'jenis_kelamin', '')) == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @if($pakaiOld)
                @error('jenis_kelamin') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Username Login</label>
            <input type="text" name="username" maxlength="50" value="{{ $nilaiLama('username', data_get($g, 'username', '')) }}"
                   placeholder="Username untuk login"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
            @if($pakaiOld)
                @error('username') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>
    </div>

    <div>
        <label class="block font-bold text-slate-700 mb-1">Alamat Tempat Tinggal</label>
        <textarea name="alamat" rows="2" maxlength="500" placeholder="Alamat lengkap guru (maksimal 500 karakter)..."
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">{{ $nilaiLama('alamat', data_get($g, 'alamat', '')) }}</textarea>
        @if($pakaiOld)
            @error('alamat') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        @endif
    </div>

    {{-- Revisi 2: lock guru ke 1 kelas (diatur admin, bisa diubah lagi di sini). --}}
    <div class="pt-2 border-t border-slate-200">
        <label class="block font-bold text-slate-700 mb-1">Kelas yang Diampu (Lock 1 Kelas)</label>
        <select name="id_rooms" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
            <option value="">-- Belum di-lock ke kelas mana pun --</option>
            @foreach(($kelasList ?? collect()) as $kelas)
                <option value="{{ $kelas->id_rooms }}" {{ (string) $nilaiLama('id_rooms', data_get($g, 'id_rooms', '')) === (string) $kelas->id_rooms ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
            @endforeach
        </select>
        <p class="text-[10px] text-slate-400 mt-1">Guru yang sudah di-lock hanya bisa membuat ujian untuk kelas ini. Kosongkan kalau belum mau mengunci.</p>
        @if($pakaiOld)
            @error('id_rooms') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        @endif
    </div>

    {{-- Revisi 3: mapel yang boleh diajar guru ini (diatur admin). --}}
    @php
        $mapelTersedia = $g ? $g->mapel->pluck('id_mapel')->all() : [];
        // Saat edit gagal validasi, checkbox yang tidak dicentang tidak ikut terkirim,
        // jadi default-nya kosong — bukan nilai mapel lama.
        $selectedMapel = $pakaiOld ? old('mapel_ids', $g ? [] : $mapelTersedia) : $mapelTersedia;
    @endphp
    <div class="pt-2 border-t border-slate-200">
        <label class="block font-bold text-slate-700 mb-1">Mata Pelajaran yang Diajar</label>
        <p class="text-[10px] text-slate-400 mb-2">Centang mapel yang boleh diajar guru ini. Kalau tidak ada yang dicentang, guru mengikuti mapel dari jadwal mengajarnya (perilaku lama).</p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach(($mapelList ?? collect()) as $mapel)
                <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg hover:border-primary cursor-pointer bg-white">
                    <input type="checkbox" name="mapel_ids[]" value="{{ $mapel->id_mapel }}" {{ in_array($mapel->id_mapel, (array) $selectedMapel) ? 'checked' : '' }} class="rounded text-primary focus:ring-0">
                    <span class="font-medium text-slate-700">
                        {{ $mapel->nama_mapel }}
                        {{-- Mapel kini per-kelas, tampilkan kelasnya biar tidak ganda arti. --}}
                        @if($mapel->kelas)
                            <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 rounded px-1 py-0.5">{{ $mapel->kelas->nama_kelas }}</span>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
        @if($pakaiOld)
            @error('mapel_ids') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-200">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Password {{ $g ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
            <input type="password" name="password" {{ $g ? '' : 'required' }} placeholder="Minimal 6 karakter"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
            @if($pakaiOld)
                @error('password') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
            @endif
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" {{ $g ? '' : 'required' }} placeholder="Ketik ulang password"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary bg-white">
        </div>
    </div>
</div>
