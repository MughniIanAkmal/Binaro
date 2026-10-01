{{-- ================================================================
     rekap-ujian/modals.blade.php
     Modal partials untuk halaman Rekap Ujian Guru
     Include via: @include('rekap-ujian.modals')
     ================================================================ --}}

{{-- =============================================
     MODAL: Detail Siswa
     ============================================= --}}
<div id="modal-detail" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#13527D] to-[#1e6fa8] px-6 py-5 flex items-center gap-4">
            <div id="modal-detail-inisial" class="w-12 h-12 rounded-2xl bg-white/20 text-white font-black text-base flex items-center justify-center shrink-0">AL</div>
            <div class="flex-1">
                <h3 id="modal-detail-nama" class="text-white font-black text-base leading-tight">–</h3>
                <p id="modal-detail-nisn" class="text-white/70 text-[11px] font-mono mt-0.5">–</p>
            </div>
            <button onclick="closeDetailModal()" class="w-8 h-8 rounded-xl bg-white/10 text-white hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        {{-- Body --}}
        <div class="p-6 space-y-5">
            {{-- Nilai + Status --}}
            <div class="flex items-center justify-between">
                <div class="text-center">
                    <div id="modal-detail-nilai" class="text-5xl font-black text-[#13527D]">–</div>
                    <div class="text-[11px] text-slate-400 mt-1">Nilai Akhir</div>
                </div>
                <span id="modal-detail-status" class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">–</span>
            </div>

            {{-- Grid Stats --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-emerald-50 rounded-xl p-3.5 border border-emerald-100">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold tracking-wide mb-1">Jawaban Benar</div>
                    <div id="modal-detail-benar" class="text-xl font-black text-emerald-600">–</div>
                </div>
                <div class="bg-rose-50 rounded-xl p-3.5 border border-rose-100">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold tracking-wide mb-1">Jawaban Salah</div>
                    <div id="modal-detail-salah" class="text-xl font-black text-rose-500">–</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold tracking-wide mb-1">Waktu Pengerjaan</div>
                    <div id="modal-detail-waktu" class="text-sm font-bold text-slate-700">–</div>
                </div>
                <div class="bg-amber-50 rounded-xl p-3.5 border border-amber-100">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold tracking-wide mb-1">Nilai Keaktifan</div>
                    <div id="modal-detail-keaktifan" class="text-sm font-bold text-amber-600">–</div>
                </div>
            </div>

            {{-- Umpan Balik --}}
            <div class="bg-sky-50 rounded-xl p-3.5 border border-sky-100">
                <div class="text-[10px] text-slate-500 uppercase font-semibold tracking-wide mb-1.5">Catatan Umpan Balik Guru</div>
                <div id="modal-detail-feedback" class="text-xs text-slate-700 leading-relaxed italic">–</div>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex gap-2 pt-1">
                <button onclick="closeDetailModal()" class="flex-1 py-2.5 text-xs font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition">Tutup</button>
                <button type="button" onclick="closeDetailModal(); openFeedbackModal(currentDetailData?.id, currentDetailData?.nama, currentDetailData?.feedback)"
                    class="flex-1 py-2.5 text-xs font-semibold bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition">
                    <i class="fas fa-pen mr-1"></i> Beri Umpan Balik
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =============================================
     MODAL: Umpan Balik Guru
     ============================================= --}}
<div id="modal-feedback" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="bg-amber-500 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-white font-black text-sm">Umpan Balik Guru</h3>
                <p id="modal-feedback-nama" class="text-white/80 text-[11px] mt-0.5">–</p>
            </div>
            <button type="button" onclick="closeFeedbackModal()" class="w-8 h-8 rounded-xl bg-white/10 text-white hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('rekap_ujian.feedback') }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="id_hasil" id="modal-feedback-id">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1.5">Catatan untuk Siswa</label>
                <textarea name="catatan_guru" id="modal-feedback-text" rows="4" required
                    placeholder="Tulis catatan umpan balik yang membangun untuk siswa..."
                    class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-amber-400 resize-none bg-slate-50 transition"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeFeedbackModal()" class="flex-1 py-2.5 text-xs font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-submit-feedback"
                    class="flex-1 py-2.5 text-xs font-semibold bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =============================================
     MODAL: Kirim Nilai ke Siswa
     ============================================= --}}
<div id="modal-kirim" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="bg-emerald-600 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-white font-black text-sm">Kirim Nilai ke Siswa</h3>
                <p id="modal-kirim-nama" class="text-white/80 text-[11px] mt-0.5">–</p>
            </div>
            <button type="button" onclick="closeKirimModal()" class="w-8 h-8 rounded-xl bg-white/10 text-white hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('rekap_ujian.kirim') }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="id_hasil" id="modal-kirim-id">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-info-circle text-emerald-500 mt-0.5"></i>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Nilai akhir dan umpan balik akan dikirimkan ke akun siswa sebagai notifikasi. Siswa dapat melihatnya di portal siswa mereka.
                </p>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1.5">Pesan Tambahan (opsional)</label>
                <textarea name="pesan_tambahan" rows="3" placeholder="Tambahkan pesan motivasi atau instruksi..."
                    class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400 resize-none bg-slate-50"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeKirimModal()" class="flex-1 py-2.5 text-xs font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition">Batal</button>
                <button type="submit"
                    class="flex-1 py-2.5 text-xs font-semibold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition">
                    <i class="fas fa-paper-plane mr-1"></i> Kirim Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =============================================
     JAVASCRIPT: Modal open/close functions
     ============================================= --}}
<script>
    // ---------- Modal: Detail Siswa ----------
    let currentDetailData = null;

    function openDetailModal(data) {
        currentDetailData = data;
        document.getElementById('modal-detail-nama').textContent      = data.nama;
        document.getElementById('modal-detail-nisn').textContent      = 'NISN: ' + data.nisn;
        document.getElementById('modal-detail-nilai').textContent     = data.nilai;
        document.getElementById('modal-detail-benar').textContent     = data.benar;
        document.getElementById('modal-detail-salah').textContent     = data.salah;
        document.getElementById('modal-detail-waktu').textContent     = data.waktu;
        document.getElementById('modal-detail-keaktifan').textContent = data.keaktifan;
        document.getElementById('modal-detail-feedback').textContent  = data.feedback;
        document.getElementById('modal-detail-inisial').textContent   = data.nama.substring(0, 2).toUpperCase();

        const badge = document.getElementById('modal-detail-status');
        badge.textContent = data.status;
        badge.className = data.status === 'LULUS KKM'
            ? 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200'
            : 'px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-600 border border-rose-200';

        document.getElementById('modal-detail').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('modal-detail').classList.add('hidden');
    }

    // ---------- Modal: Umpan Balik ----------
    function validateFeedbackInput(textarea) {
        return true;
    }

    function openFeedbackModal(idHasil, nama, currentFeedback) {
        document.getElementById('modal-feedback-id').value = idHasil || '';
        document.getElementById('modal-feedback-nama').textContent = nama;
        const textarea = document.getElementById('modal-feedback-text');
        textarea.value = (currentFeedback && currentFeedback !== 'Belum ada catatan') ? currentFeedback : '';
        document.getElementById('modal-feedback').classList.remove('hidden');
    }
    function closeFeedbackModal() {
        document.getElementById('modal-feedback').classList.add('hidden');
    }

    // ---------- Modal: Kirim Pesan ----------
    function openKirimModal(idHasil, nama) {
        document.getElementById('modal-kirim-id').value = idHasil || '';
        document.getElementById('modal-kirim-nama').textContent = nama;
        document.getElementById('modal-kirim').classList.remove('hidden');
    }
    function closeKirimModal() {
        document.getElementById('modal-kirim').classList.add('hidden');
    }

    // Tutup modal saat klik backdrop
    ['modal-detail', 'modal-feedback', 'modal-kirim'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', function (e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });
</script>
