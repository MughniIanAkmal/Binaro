@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'Tambah Jadwal Pelajaran')
@section('page_subtitle', 'Lengkapi formulir di bawah untuk menambahkan jadwal mata pelajaran baru.')

@section('header_actions')
    <a href="{{ route('jadwal.index') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition shadow-xs">
        <i class="fas fa-arrow-left text-slate-400"></i>
        <span>Kembali ke Jadwal</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Card Utama (Satu Kotak Utuh) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Judul Sub-Header -->
        <div class="bg-gray-50/50 px-6 py-2.5 border-b border-gray-100">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">FORMULIR INPUT JADWAL</h2>
        </div>

        <!-- Form Tambah -->
        <form action="{{ route('jadwal.store') }}" method="POST" class="px-6 pt-2 pb-6 space-y-4">
            @csrf

            <!-- Input Hari -->
            <div>
                <label for="hari" class="block text-sm font-semibold text-gray-700 mb-1.5">Hari</label>
                <select name="hari" id="hari" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm text-gray-700" required>
                    <option value="">-- Pilih Hari --</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                        <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>
                            {{ $hari }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pilih Slot Jam Pelajaran -->
            <div class="space-y-2 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                <div class="flex items-center justify-between">
                    <label for="time_slot_id" class="block text-xs font-bold text-slate-800">
                        Slot Jam Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] font-semibold text-slate-500 bg-white px-2.5 py-0.5 rounded-full border border-slate-200">
                        <i class="fas fa-school mr-1 text-primary"></i> Tutup Sekolah: <strong class="text-slate-800">{{ $jamTutup ?? "12:00" }}</strong> WIB
                    </span>
                </div>

                <select name="time_slot_id" id="time_slot_id" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm text-gray-700 bg-white">
                    <option value="">-- Pilih Slot Jam --</option>
                    @foreach ($slots as $slot)
                        <option value="{{ $slot->id_slot }}"
                                {{ old("time_slot_id") == $slot->id_slot ? "selected" : "" }}
                                {{ $slot->is_break ? "disabled" : "" }}>
                            {{ $slot->nama }} ({{ $slot->jam_mulai }}-{{ $slot->jam_selesai }}){{ $slot->is_break ? " — istirahat" : "" }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400">
                    Slot istirahat tidak bisa dipakai. Ubah daftar slot lewat menu Pengaturan Jam Absen.
                </p>
                @error("time_slot_id")
                    <p class="text-[11px] text-rose-600 font-semibold flex items-center gap-1.5">
                        <i class="fas fa-circle-exclamation"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Input Ruang (opsional) -->
            <div>
                <label for="ruang" class="block text-sm font-semibold text-gray-700 mb-1.5">Ruang <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="text" name="ruang" id="ruang" value="{{ old("ruang") }}" maxlength="50"
                       placeholder="Contoh: Lab IPA, Aula (kosongkan jika ikut kelas)"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm text-gray-700">
                @error("ruang")
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Select Mata Pelajaran -->
            <div>
                <label for="id_mapel" class="block text-sm font-semibold text-gray-700 mb-1.5">Mata Pelajaran</label>
                <select name="id_mapel" id="id_mapel" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm text-gray-700" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach ($mapel as $m)
                        <option value="{{ $m->id_mapel }}" {{ old('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                            {{ $m->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Guru -->
            <div>
                <label for="id_guru" class="block text-sm font-semibold text-gray-700 mb-1.5">Guru Pengampu</label>
                <select name="id_guru" id="id_guru" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm text-gray-700" required>
                    <option value="">-- Pilih Guru --</option>
                    @foreach ($guru as $g)
                        <option value="{{ $g->id_guru }}" {{ old('id_guru') == $g->id_guru ? 'selected' : '' }}>
                            {{ $g->nama_guru }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Kelas -->
            <div>
                <label for="id_kelas" class="block text-sm font-semibold text-gray-700 mb-1.5">Kelas</label>
                <select name="id_kelas" id="id_kelas" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm text-gray-700" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id_rooms }}" {{ old('id_kelas') == $k->id_rooms ? 'selected' : '' }}>
                            {{ $k->pararel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                <a href="{{ route('jadwal.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" id="btn-submit-jadwal" class="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-xl text-sm font-bold transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-floppy-disk"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>


@endsection
