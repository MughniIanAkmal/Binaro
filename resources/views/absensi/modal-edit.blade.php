<!-- Modal Edit Presensi -->
<div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-sm text-slate-900">Ubah Presensi: <span id="modalSiswaName" class="text-[#13527D]"></span></h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('absensi.manual') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="id_siswa" id="modalIdSiswa">
            <input type="hidden" name="id_guru" value="{{ auth()->user()->id_guru ?? 1 }}">
            <input type="hidden" name="tanggal" value="{{ $selectedDate }}">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Kehadiran</label>
                <select name="status" id="modalStatus" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold">
                    <option value="Hadir">Hadir</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpa">Alpa / Tanpa Keterangan</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                <input type="text" name="namaKeterangan" id="modalKeterangan" placeholder="Contoh: Surat Dokter Terlampir / Acara Keluarga" 
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Upload Berkas/Surat (Opsional)</label>
                <input type="file" name="berkas_surat" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 text-[11px]">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#13527D] text-white rounded-lg font-bold hover:bg-[#0E3D5D]">Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>
