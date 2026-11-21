{{--
    Modal Pengaturan Jam Absen — dipakai bersama oleh halaman Presensi Siswa
    dan QR & Absensi. Dulu berupa halaman terpisah (/admin/absensi/settings);
    sekarang pop-up agar admin tidak kehilangan konteks tabel yang sedang dilihat.
--}}
@php
    $batasAwal = \App\Models\AbsensiSetting::get('batas_awal', '07:00');
    $batasTepat = \App\Models\AbsensiSetting::get('batas_tepat', '08:00');
    $batasTutup = \App\Models\AbsensiSetting::get('batas_tutup', '12:00');
@endphp

<div id="settingsModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4" aria-hidden="true">
    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[92vh] flex flex-col shadow-xl border border-slate-200" role="dialog" aria-modal="true" aria-labelledby="settingsModalTitle">
        <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between gap-4 shrink-0">
            <div>
                <h3 id="settingsModalTitle" class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fas fa-clock text-primary"></i> Pengaturan Jam Absen
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Ambang batas waktu presensi dan jam tutup operasional sekolah.</p>
            </div>
            <button type="button" onclick="closeSettingsModal()" class="text-2xl leading-none text-slate-400 hover:text-slate-600 transition" aria-label="Tutup">&times;</button>
        </div>

        <form action="{{ route('admin.absensi.settings.update') }}" method="POST" class="overflow-y-auto px-5 py-4 space-y-4 text-xs flex-1">
            @csrf

            {{-- Ringkasan 4 zona: cepat dibaca sebelum mengubah angka. --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-xl bg-sky-50 border border-sky-200 px-3 py-2">
                    <div class="text-[9px] font-black uppercase tracking-wider text-sky-700">Lebih Awal</div>
                    <div class="font-black text-sky-900">&lt; <span data-zone="awal">{{ $batasAwal }}</span> WIB</div>
                </div>
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-2">
                    <div class="text-[9px] font-black uppercase tracking-wider text-emerald-700">Tepat Waktu</div>
                    <div class="font-black text-emerald-900"><span data-zone="awal">{{ $batasAwal }}</span>–<span data-zone="tepat">{{ $batasTepat }}</span></div>
                </div>
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-3 py-2">
                    <div class="text-[9px] font-black uppercase tracking-wider text-amber-700">Terlambat</div>
                    <div class="font-black text-amber-900"><span data-zone="tepat">{{ $batasTepat }}</span>–<span data-zone="tutup">{{ $batasTutup }}</span></div>
                </div>
                <div class="rounded-xl bg-rose-50 border border-rose-200 px-3 py-2">
                    <div class="text-[9px] font-black uppercase tracking-wider text-rose-700">Sekolah Tutup</div>
                    <div class="font-black text-rose-900">&gt; <span data-zone="tutup">{{ $batasTutup }}</span> WIB</div>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">1. Jam Datang Lebih Awal <span class="text-rose-500">*</span></label>
                <input type="time" name="batas_awal" id="settings_batas_awal" required value="{{ old('batas_awal', $batasAwal) }}"
                       oninput="syncSettingsPreview()"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-primary focus:bg-white transition">
                <p class="text-[10px] text-slate-400 mt-1">Scan sebelum jam ini = "Datang Lebih Awal".</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">2. Batas Hadir Tepat Waktu <span class="text-rose-500">*</span></label>
                <input type="time" name="batas_tepat" id="settings_batas_tepat" required value="{{ old('batas_tepat', $batasTepat) }}"
                       oninput="syncSettingsPreview()"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-primary focus:bg-white transition">
                <p class="text-[10px] text-slate-400 mt-1">Rentang jam awal sampai jam ini dihitung "Tepat Waktu".</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">3. Jam Tutup Sekolah <span class="text-rose-500">*</span></label>
                <input type="time" name="batas_tutup" id="settings_batas_tutup" required value="{{ old('batas_tutup', $batasTutup) }}"
                       oninput="syncSettingsPreview()"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-primary focus:bg-white transition">
                <p class="text-[10px] text-slate-400 mt-1">Sekaligus menjadi batas maksimal jam selesai pada Jadwal Pelajaran.</p>
            </div>

            <div id="settings-live-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-semibold flex items-start gap-2">
                <i class="fas fa-triangle-exclamation text-rose-600 mt-0.5"></i>
                <span id="settings-live-error-text"></span>
            </div>

            @if($errors->has('batas_awal') || $errors->has('batas_tepat') || $errors->has('batas_tutup'))
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-semibold space-y-1">
                    @foreach(['batas_awal', 'batas_tepat', 'batas_tutup'] as $field)
                        @error($field) <div>{{ $message }}</div> @enderror
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeSettingsModal()"
                        class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="btn-save-settings"
                        class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl font-bold shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-floppy-disk mr-1"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSettingsModal() {
        const m = document.getElementById('settingsModal');
        if (!m) return;
        m.classList.remove('hidden');
        m.classList.add('flex');
        m.setAttribute('aria-hidden', 'false');
        syncSettingsPreview();
    }

    function closeSettingsModal() {
        const m = document.getElementById('settingsModal');
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
        m.setAttribute('aria-hidden', 'true');
    }

    /** Sinkronkan ringkasan zona + validasi urutan jam sebelum disimpan. */
    function syncSettingsPreview() {
        const awal = document.getElementById('settings_batas_awal');
        const tepat = document.getElementById('settings_batas_tepat');
        const tutup = document.getElementById('settings_batas_tutup');
        const errBox = document.getElementById('settings-live-error');
        const errText = document.getElementById('settings-live-error-text');
        const btn = document.getElementById('btn-save-settings');
        if (!awal || !tepat || !tutup) return;

        document.querySelectorAll('[data-zone="awal"]').forEach((el) => { el.textContent = awal.value || '-'; });
        document.querySelectorAll('[data-zone="tepat"]').forEach((el) => { el.textContent = tepat.value || '-'; });
        document.querySelectorAll('[data-zone="tutup"]').forEach((el) => { el.textContent = tutup.value || '-'; });

        let pesan = '';
        if (awal.value && tepat.value && awal.value >= tepat.value) {
            pesan = 'Jam tepat waktu harus lebih lambat dari jam datang lebih awal.';
        } else if (tepat.value && tutup.value && tepat.value >= tutup.value) {
            pesan = 'Jam tutup sekolah harus lebih lambat dari jam tepat waktu.';
        }

        if (pesan) {
            errText.textContent = pesan;
            errBox.classList.remove('hidden');
            btn.disabled = true;
        } else {
            errBox.classList.add('hidden');
            btn.disabled = false;
        }
    }

    document.addEventListener('keydown', function (event) {
        const m = document.getElementById('settingsModal');
        if (event.key === 'Escape' && m && !m.classList.contains('hidden')) {
            closeSettingsModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const m = document.getElementById('settingsModal');
        if (m) {
            m.addEventListener('click', function (event) {
                if (event.target === m) closeSettingsModal();
            });
        }

        // Buka otomatis saat kembali dari validasi gagal atau dari tautan lama.
        @if($errors->has('batas_awal') || $errors->has('batas_tepat') || $errors->has('batas_tutup') || request('open_settings'))
            openSettingsModal();
        @endif
    });
</script>
