@extends('layouts.guru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Manajemen Materi Pembelajaran</h1>
            <p class="text-xs text-slate-500">Kelola Bab, Sub-Bab, dan Tipe Konten (Video, PDF, Kuis)</p>
        </div>

        <!-- Filter Mapel -->
        <form method="GET" action="{{ route('guru.materi.index') }}" class="flex items-center gap-2">
            <select name="mapel_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#13527D]">
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($mapels as $mapel)
                    <option value="{{ $mapel->id_mapel }}" {{ $selectedMapelId == $mapel->id_mapel ? 'selected' : '' }}>
                        {{ $mapel->nama_mapel }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>


    @if(!$selectedMapelId)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
        <i class="fas fa-book-open text-slate-300 text-3xl mb-2"></i>
        <p class="text-xs text-slate-500">Silakan pilih Mata Pelajaran di atas untuk melihat atau mengelola materi.</p>
    </div>
    @else
    <!-- Action Bar -->
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-800">Daftar Bab & Konten</h2>
        <button onclick="openModal('modal-add-bab')" class="px-3 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-medium rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah Bab Baru
        </button>
    </div>

    <!-- Bab Tree View -->
    <div class="space-y-4">
        @forelse($babs as $babIndex => $bab)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Bab Header -->
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-[#13527D]/10 text-[#13527D] text-xs font-bold rounded-lg flex items-center justify-center">
                        {{ $babIndex + 1 }}
                    </span>
                    <h3 class="text-sm font-bold text-slate-800">{{ $bab->nama_bab }}</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="openModalAddSubBab({{ $bab->id_bab }}, '{{ $bab->nama_bab }}')" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-medium rounded-lg transition flex items-center gap-1.5">
                        <i class="fas fa-plus text-[10px] text-[#13527D]"></i> Sub-Bab
                    </button>
                    <form action="{{ route('guru.materi.destroy.bab', $bab->id_bab) }}" method="POST" onsubmit="return confirm('Hapus Bab ini beserta seluruh materi di dalamnya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                            <i class="fas fa-trash-can text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sub-Bab List -->
            <div class="p-4 space-y-3">
                @forelse($bab->subBab as $sub)
                <div class="bg-slate-50/50 rounded-lg border border-slate-100 p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-folder-tree text-slate-400 text-xs"></i>
                            <h4 class="text-xs font-semibold text-slate-700">{{ $sub->nama_sub_bab }}</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="openModalAddMateri({{ $sub->id_sub_bab }}, '{{ $sub->nama_sub_bab }}')" class="text-[11px] text-[#13527D] hover:underline font-medium flex items-center gap-1">
                                <i class="fas fa-plus text-[9px]"></i> Tambah Materi
                            </button>
                            <form action="{{ route('guru.materi.destroy.sub-bab', $sub->id_sub_bab) }}" method="POST" onsubmit="return confirm('Hapus Sub-Bab ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Materi Items -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 pt-1">
                        @forelse($sub->materi as $mat)
                        <div class="bg-white p-3 rounded-lg border border-slate-200 flex flex-col justify-between space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    @if($mat->tipe_materi === 'video')
                                        <span class="w-6 h-6 bg-rose-50 text-rose-600 rounded flex items-center justify-center text-[10px]"><i class="fas fa-play"></i></span>
                                    @elseif($mat->tipe_materi === 'dokumen')
                                        <span class="w-6 h-6 bg-blue-50 text-blue-600 rounded flex items-center justify-center text-[10px]"><i class="fas fa-file-pdf"></i></span>
                                    @else
                                        <span class="w-6 h-6 bg-amber-50 text-amber-600 rounded flex items-center justify-center text-[10px]"><i class="fas fa-clipboard-question"></i></span>
                                    @endif
                                    <span class="text-xs font-medium text-slate-800 line-clamp-1">{{ $mat->judul_materi }}</span>
                                </div>
                                <form action="{{ route('guru.materi.destroy', $mat->id_materi) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-300 hover:text-rose-600 text-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-slate-100">
                                <span class="uppercase font-semibold tracking-wider text-[9px] px-1.5 py-0.5 rounded bg-slate-100">
                                    {{ $mat->tipe_materi }}
                                </span>
                                @if($mat->tipe_materi === 'video' && $mat->url_video)
                                    <a href="{{ $mat->url_video }}" target="_blank" class="text-[#13527D] hover:underline flex items-center gap-1">
                                        <i class="fas fa-external-link-alt text-[9px]"></i> Link
                                    </a>
                                @elseif($mat->tipe_materi === 'dokumen' && $mat->file_pdf)
                                    <a href="{{ route('guru.materi.sub_bab', $sub->id_sub_bab) }}" class="text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1">
                                        <i class="fas fa-file-pdf text-[9px]"></i> Preview PDF
                                    </a>
                                @elseif($mat->tipe_materi === 'kuis')
                                    @if($mat->id_quiz)
                                    <a href="{{ route('guru.quiz.bank', $mat->id_quiz) }}" class="text-[#13527D] font-bold hover:underline flex items-center gap-1">
                                        <i class="fas fa-edit text-[9px]"></i> Kelola Bank Soal
                                    </a>
                                    @else
                                    <span class="text-slate-400">Bank Soal Auto</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-[11px] text-slate-400 italic col-span-full py-1">Belum ada materi di Sub-Bab ini.</p>
                        @endforelse
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic text-center py-2">Belum ada Sub-Bab.</p>
                @endforelse
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
            <p class="text-xs text-slate-500 mb-3">Belum ada Bab pada Mata Pelajaran ini.</p>
            <button onclick="openModal('modal-add-bab')" class="px-3 py-2 bg-[#13527D] text-white text-xs font-medium rounded-lg">
                <i class="fas fa-plus"></i> Tambah Bab Pertama
            </button>
        </div>
        @endforelse
    </div>
    @endif
</div>

<!-- Modal Tambah Bab -->
<div id="modal-add-bab" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Tambah Bab Baru</h3>
            <button onclick="closeModal('modal-add-bab')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('guru.materi.store.bab') }}" method="POST" onsubmit="return validateIndexFormNoSymbols('index-nama-bab', 'Nama Bab')" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="id_mapel" value="{{ $selectedMapelId }}">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Bab <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_bab" id="index-nama-bab" required maxlength="100" placeholder="Contoh: Bab 1 Operasi Hitung" oninput="sanitizeIndexInput(this, 'counter-index-bab')" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
                <div class="flex items-center justify-between mt-1 text-[10px]">
                    <span class="text-slate-500"><i class="fas fa-shield-halved mr-1 text-[#13527D]"></i>Hanya huruf, angka, dan spasi (tanpa simbol).</span>
                    <span id="counter-index-bab" class="font-bold text-slate-600">0/100</span>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('modal-add-bab')" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg">Batal</button>
                <button type="submit" class="px-3 py-2 bg-[#13527D] text-white font-semibold rounded-lg">Simpan Bab</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Sub-Bab -->
<div id="modal-add-subbab" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Tambah Sub-Bab</h3>
            <button onclick="closeModal('modal-add-subbab')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('guru.materi.store.sub-bab') }}" method="POST" onsubmit="return validateIndexFormNoSymbols('index-nama-subbab', 'Nama Sub-Bab')" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" id="subbab-id-bab" name="id_bab">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Sub-Bab <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_sub_bab" id="index-nama-subbab" required maxlength="100" placeholder="Contoh: Sub Bab 1 Penjumlahan" oninput="sanitizeIndexInput(this, 'counter-index-subbab')" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
                <div class="flex items-center justify-between mt-1 text-[10px]">
                    <span class="text-slate-500"><i class="fas fa-shield-halved mr-1 text-[#13527D]"></i>Hanya huruf, angka, dan spasi (tanpa simbol).</span>
                    <span id="counter-index-subbab" class="font-bold text-slate-600">0/100</span>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('modal-add-subbab')" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg">Batal</button>
                <button type="submit" class="px-3 py-2 bg-[#13527D] text-white font-semibold rounded-lg">Simpan Sub-Bab</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Materi -->
<div id="modal-add-materi" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Tambah Materi Konten</h3>
            <button onclick="closeModal('modal-add-materi')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('guru.materi.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return validateIndexFormMateri()" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" id="materi-id-subbab" name="id_sub_bab">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Judul / Nama Materi (Video / PDF / Kuis) <span class="text-rose-500">*</span></label>
                <input type="text" name="judul_materi" id="index-judul-materi" required maxlength="100" placeholder="Contoh: Video Penjelasan Perkalian" oninput="sanitizeIndexInput(this, 'counter-index-materi')" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
                <div class="flex items-center justify-between mt-1 text-[10px]">
                    <span class="text-slate-500"><i class="fas fa-shield-halved mr-1 text-[#13527D]"></i>Hanya huruf, angka, dan spasi (tanpa simbol).</span>
                    <span id="counter-index-materi" class="font-bold text-slate-600">0/100</span>
                </div>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tipe Konten Materi</label>
                <select id="tipe_materi_select" name="tipe_materi" onchange="toggleMateriFields()" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
                    <option value="video">Video (YouTube / MP4 URL)</option>
                    <option value="dokumen">Dokumen PDF (Max 10MB)</option>
                    <option value="kuis">Kuis Interaktif</option>
                </select>
            </div>

            <!-- Field Video -->
            <div id="field-video" class="space-y-1">
                <label class="block font-semibold text-slate-700">URL Link Video (YouTube / MP4) <span class="text-rose-500">*</span></label>
                <input type="url" name="url_video" id="index-url-video" onblur="validateIndexVideoUrl(this)" placeholder="https://www.youtube.com/watch?v=... atau https://example.com/video.mp4" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
                <p class="text-[10px] text-amber-700 font-semibold"><i class="fas fa-triangle-exclamation mr-1"></i>Hanya menerima link YouTube atau file .mp4. Link Google Drive dilarang dan akan ditolak.</p>
            </div>

            <!-- Field PDF -->
            <div id="field-dokumen" class="space-y-1 hidden">
                <label class="block font-semibold text-slate-700">Upload File PDF (Max 10MB)</label>
                <input type="file" name="file_pdf" accept="application/pdf,.pdf" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:outline-none">
            </div>

            <!-- Field Kuis -->
            <div id="field-kuis" class="p-3 bg-emerald-50/60 border border-emerald-200 rounded-lg text-emerald-800 text-[11px] hidden space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold"><i class="fas fa-circle-question mr-1"></i> Bank Soal Kuis</span>
                    <a href="{{ route('guru.quiz.template.download') }}" class="text-[11px] font-bold text-[#13527D] hover:underline">
                        <i class="fas fa-download mr-0.5"></i> Unduh Template Excel (.xlsx)
                    </a>
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Unggah Soal File Excel / CSV (.xlsx / .xls / .csv) (Opsional):</label>
                    <input type="file" name="file_excel" id="index-file-excel" accept=".xlsx, .xls, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, text/csv" onchange="validateIndexExcel(this)" class="w-full px-3 py-1.5 bg-white border border-emerald-300 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-500 mt-1">Hanya menerima format spreadsheet Excel (.xlsx, .xls) atau CSV (.csv). Format selain Excel/CSV ditolak.</p>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-materi')" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg">Batal</button>
                <button type="submit" class="px-3 py-2 bg-[#13527D] text-white font-semibold rounded-lg">Simpan Materi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
function openModalAddSubBab(babId, babName) {
    document.getElementById('subbab-id-bab').value = babId;
    openModal('modal-add-subbab');
}
function openModalAddMateri(subBabId, subBabName) {
    document.getElementById('materi-id-subbab').value = subBabId;
    openModal('modal-add-materi');
    toggleMateriFields();
}
function toggleMateriFields() {
    const val = document.getElementById('tipe_materi_select').value;
    document.getElementById('field-video').classList.toggle('hidden', val !== 'video');
    document.getElementById('field-dokumen').classList.toggle('hidden', val !== 'dokumen');
    document.getElementById('field-kuis').classList.toggle('hidden', val !== 'kuis');
}

function sanitizeIndexInput(el, counterId) {
    if (!el) return;
    const cleaned = el.value.replace(/[^a-zA-Z0-9\s]/g, '');
    if (el.value !== cleaned) el.value = cleaned;
    if (counterId) {
        const c = document.getElementById(counterId);
        if (c) {
            c.textContent = `${el.value.length}/100`;
            c.className = el.value.length >= 100 ? 'font-bold text-rose-600' : 'font-bold text-slate-600';
        }
    }
}

function validateIndexFormNoSymbols(inputId, fieldName) {
    const el = document.getElementById(inputId);
    if (!el) return true;
    sanitizeIndexInput(el);
    const val = el.value.trim();
    if (val.length < 3) {
        alert(`${fieldName} minimal 3 karakter.`);
        el.focus();
        return false;
    }
    if (val.length > 100) {
        alert(`${fieldName} maksimal 100 karakter.`);
        el.focus();
        return false;
    }
    if (/[^a-zA-Z0-9\s]/.test(val)) {
        alert(`${fieldName} tidak boleh mengandung simbol.`);
        el.focus();
        return false;
    }
    return true;
}

function validateIndexVideoUrl(input) {
    if (!input || !input.value.trim()) return true;
    const val = input.value.trim().toLowerCase();
    if (val.includes('drive.google.com') || val.includes('docs.google.com') || val.includes('google.com/file') || val.includes('google.com/drive')) {
        alert('Tautan Google Drive dilarang!\n\nHanya diperbolehkan tautan YouTube atau file video .mp4 langsung.');
        input.value = '';
        input.focus();
        return false;
    }
    const isYt = val.includes('youtube.com/watch') || val.includes('youtu.be/') || val.includes('youtube.com/embed') || val.includes('youtube.com/shorts');
    const isMp4 = val.endsWith('.mp4') || val.includes('.mp4?');
    if (!isYt && !isMp4) {
        alert('URL Video tidak sah! Hanya link YouTube resmi atau file .mp4 langsung.');
        input.focus();
        return false;
    }
    return true;
}

function validateIndexExcel(input) {
    if (!input || !input.files || !input.files[0]) return true;
    const name = input.files[0].name.toLowerCase();
    if (!name.endsWith('.xlsx') && !name.endsWith('.xls')) {
        alert('Format file ditolak!\n\nBagian kuis HANYA menerima format Excel (.xlsx atau .xls).');
        input.value = '';
        return false;
    }
    return true;
}

function validateIndexFormMateri() {
    if (!validateIndexFormNoSymbols('index-judul-materi', 'Nama Materi')) return false;
    const tipe = document.getElementById('tipe_materi_select').value;
    if (tipe === 'video') {
        const v = document.getElementById('index-url-video');
        if (v && v.value.trim() && !validateIndexVideoUrl(v)) return false;
    }
    if (tipe === 'kuis') {
        const x = document.getElementById('index-file-excel');
        if (x && x.files && x.files[0] && !validateIndexExcel(x)) return false;
    }
    return true;
}
</script>
@endsection
