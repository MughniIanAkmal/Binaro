@extends('layouts.guru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('guru.materi.index') }}" class="text-xs text-[#13527D] hover:underline flex items-center gap-1 font-medium mb-1">
                <i class="fas fa-arrow-left text-[10px]"></i> Kembali ke Materi
            </a>
            <h1 class="text-xl font-bold text-slate-900">Bank Soal: {{ $quiz->judul_quiz }}</h1>
            <p class="text-xs text-slate-500">
                {{ $quiz->subBab?->bab?->mataPelajaran?->nama_mapel }} &bull; {{ $quiz->subBab?->nama_sub_bab }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="openModal('modal-import-soal')" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-file-excel"></i> Import Excel / CSV
            </button>
            <button onclick="openModal('modal-add-soal')" class="px-3 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-plus"></i> Tambah Soal Manual
            </button>
        </div>
    </div>


    <!-- Question List -->
    <div class="space-y-3">
        @forelse($quiz->soal as $idx => $soal)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 bg-[#13527D]/10 text-[#13527D] text-xs font-bold rounded-lg flex items-center justify-center shrink-0">
                        {{ $idx + 1 }}
                    </span>
                    <p class="text-xs font-bold text-slate-800 pt-0.5 leading-relaxed">
                        {{ $soal->pertanyaan }}
                    </p>
                </div>
                <form action="{{ route('guru.quiz.destroy.soal', $soal->id_soal) }}" method="POST" onsubmit="return confirm('Hapus soal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-300 hover:text-rose-600 text-xs">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 pl-9 text-xs">
                @foreach(['A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c, 'D' => $soal->opsi_d] as $k => $v)
                <div class="p-2 rounded-lg border {{ $soal->kunci_jawaban === $k ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-900' : 'border-slate-100 bg-slate-50 text-slate-700' }}">
                    <span class="text-[10px] text-slate-400 mr-1">{{ $k }}.</span> {{ $v }}
                    @if($soal->kunci_jawaban === $k)
                        <i class="fas fa-check text-emerald-600 ml-1 text-[10px]"></i>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center text-xs text-slate-400">
            Belum ada soal dalam bank kuis ini. Silakan tambah manual atau import via Excel.
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Tambah Soal Manual -->
<div id="modal-add-soal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-5 border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Tambah Soal Manual</h3>
            <button onclick="closeModal('modal-add-soal')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('guru.quiz.store.soal', $quiz->id_quiz) }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Pertanyaan</label>
                <textarea name="pertanyaan" required rows="2" maxlength="1000" placeholder="Tuliskan soal (maksimal 1000 karakter)..." class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Opsi A</label>
                    <input type="text" name="opsi_a" required maxlength="255" placeholder="Pilihan A" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Opsi B</label>
                    <input type="text" name="opsi_b" required maxlength="255" placeholder="Pilihan B" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Opsi C</label>
                    <input type="text" name="opsi_c" required maxlength="255" placeholder="Pilihan C" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Opsi D</label>
                    <input type="text" name="opsi_d" required maxlength="255" placeholder="Pilihan D" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kunci Jawaban</label>
                <select name="kunci_jawaban" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-add-soal')" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg">Batal</button>
                <button type="submit" class="px-3 py-2 bg-[#13527D] text-white font-semibold rounded-lg">Simpan Soal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel Only -->
<div id="modal-import-soal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Import Soal Excel / CSV (.xlsx / .xls / .csv)</h3>
            <button onclick="closeModal('modal-import-soal')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('guru.quiz.import', $quiz->id_quiz) }}" method="POST" enctype="multipart/form-data" onsubmit="return validateBankExcelForm()" class="space-y-4 text-xs">
            @csrf
            <div class="p-3 bg-emerald-50/60 border border-emerald-200 rounded-lg space-y-2">
                <p class="font-bold text-emerald-900">Format Template Soal Excel / CSV:</p>
                <a href="{{ route('guru.quiz.template.download') }}" class="inline-flex items-center gap-1.5 text-xs text-[#13527D] font-bold hover:underline">
                    <i class="fas fa-file-excel text-emerald-600"></i> Download Template Excel (.xlsx)
                </a>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Unggah File Soal Excel / CSV (.xlsx / .xls / .csv) <span class="text-rose-500">*</span></label>
                <input type="file" name="file_excel" id="bank-file-excel" accept=".xlsx, .xls, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, text/csv" required onchange="validateBankExcel(this)" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:border-[#13527D]">
                <p class="text-[10px] text-slate-500 mt-1">Menerima format spreadsheet Excel (.xlsx, .xls) atau CSV (.csv). Maksimal 50 soal per file. Format file selain Excel/CSV ditolak.</p>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-import-soal')" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg">Batal</button>
                <button type="submit" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg">Proses Import</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function validateBankExcel(input) {
    if (!input || !input.files || !input.files[0]) return true;
    const file = input.files[0];
    const name = file.name.toLowerCase();
    if (!name.endsWith('.xlsx') && !name.endsWith('.xls') && !name.endsWith('.csv')) {
        alert('Format file ditolak!\n\nBagian kuis HANYA menerima berkas spreadsheet Excel (.xlsx, .xls) atau CSV (.csv).\nFile "' + file.name + '" bukan berkas Excel/CSV yang diizinkan.');
        input.value = '';
        return false;
    }
    return true;
}

function validateBankExcelForm() {
    const input = document.getElementById('bank-file-excel');
    return validateBankExcel(input);
}
</script>
@endsection
