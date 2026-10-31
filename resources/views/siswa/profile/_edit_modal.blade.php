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