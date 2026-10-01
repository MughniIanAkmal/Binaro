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