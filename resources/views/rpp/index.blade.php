@extends('layouts.app')

@section('content')
<!-- Content Body -->
        <div class="p-8 space-y-6">
            <!-- Page Title -->
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">Persetujuan & Kelola RPP Guru</h2>
                    <p class="text-xs text-slate-500">Dashboard &gt; Persetujuan Modul Ajar (RPP Guru) &bull; Khusus Meninjau & Menyetujui Pengajuan Guru</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 flex items-center gap-1.5 shadow-xs">
                        <i class="fas fa-download mr-1"></i> Cetak / Export
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start">
                    <div>
                        <div class="text-2xl font-black text-slate-900">{{ $kpi['total'] }}</div>
                        <div class="text-xs text-slate-600 font-semibold mt-1">Total RPP</div>
                        <div class="text-[11px] text-slate-400">Target Modul Ajar</div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center text-sm"><i class="fas fa-book"></i></div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start">
                    <div>
                        <div class="text-2xl font-black text-slate-900">{{ $kpi['terverifikasi'] }}</div>
                        <div class="text-xs text-slate-600 font-semibold mt-1">Terverifikasi</div>
                        <div class="text-[11px] text-slate-400">Sudah diverifikasi</div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm"><i class="fas fa-check"></i></div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start">
                    <div>
                        <div class="text-2xl font-black text-slate-900">{{ $kpi['perlu_review'] }}</div>
                        <div class="text-xs text-slate-600 font-semibold mt-1">Perlu Review</div>
                        <div class="text-[11px] text-slate-400">Menunggu peninjauan</div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center text-sm"><i class="fas fa-clock"></i></div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <!-- Table Header Filter -->
                <div class="p-4 border-b border-slate-200 flex gap-3 items-center flex-wrap">
                    <a href="{{ route('rpp.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-[#13527D] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
                    <a href="{{ route('rpp.index', ['status' => 'menunggu_review']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 {{ request('status') == 'menunggu_review' ? 'bg-[#13527D] text-white' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100' }}">
                        <i class="fas fa-inbox text-[11px] text-amber-500"></i>
                        <span>Permintaan Guru (Menunggu Review)</span>
                        @if(($kpi['perlu_review'] ?? 0) > 0)
                            <span class="bg-amber-400 text-slate-900 text-[10px] font-black px-1.5 py-0.2 rounded-full">{{ $kpi['perlu_review'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('rpp.index', ['status' => 'terverifikasi']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') == 'terverifikasi' ? 'bg-[#13527D] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Terverifikasi (Disetujui)</a>
                    <a href="{{ route('rpp.index', ['status' => 'perlu_revisi']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') == 'perlu_revisi' ? 'bg-[#13527D] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Perlu Revisi</a>
                    <a href="{{ route('rpp.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') == 'draft' ? 'bg-[#13527D] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Draf Guru</a>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                                <th class="p-3.5 pl-6">NO</th>
                                <th class="p-3.5">MATA PELAJARAN & TOPIK</th>
                                <th class="p-3.5">KELAS & FASE</th>
                                <th class="p-3.5">GURU PENGAJAR</th>
                                <th class="p-3.5">KELENGKAPAN MODUL</th>
                                <th class="p-3.5">STATUS SUPERVISI</th>
                                <th class="p-3.5 text-center">AKSI SUPERVISI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($rppList as $i => $rpp)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 pl-6 text-slate-400 font-semibold">{{ $rppList->firstItem() + $i }}</td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $rpp->mataPelajaran->nama_mapel ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">Topik: {{ $rpp->judul_rpp }}</div>
                                </td>
                                <td class="p-3.5 font-medium text-slate-700">{{ $rpp->kelas->pararel ?? '-' }}</td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $rpp->guru->nama_guru ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">NIP. {{ $rpp->guru->nip ?? '-' }}</div>
                                </td>
                                <td class="p-3.5">
                                    <div class="flex gap-1 flex-wrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ ($rpp->komponen_checklist['tujuan'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">Tujuan</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ ($rpp->komponen_checklist['video'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">Video SD</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ ($rpp->komponen_checklist['kktp'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">KKTP</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ ($rpp->komponen_checklist['lkpd'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">LKPD</span>
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    @if($rpp->status == 'terverifikasi')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"><i class="fas fa-check text-[9px]"></i> Terverifikasi</span>
                                    @elseif($rpp->status == 'menunggu_review')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800"><i class="fas fa-clock text-[9px]"></i> Menunggu Review</span>
                                    @elseif($rpp->status == 'perlu_revisi')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"><i class="fas fa-exclamation-triangle text-[9px]"></i> Perlu Revisi</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800"><i class="fas fa-times text-[9px]"></i> Draf Belum Lengkap</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5 flex-nowrap">
                                        {{-- 1. Tombol Terima (Accept) jika menunggu review / revisi / draft --}}
                                        @if(in_array($rpp->status, ['menunggu_review', 'perlu_revisi', 'draft']))
                                            <form action="{{ route('rpp.updateStatus', $rpp->id_rpp) }}" method="POST" class="inline-block" onsubmit="return confirm('Accept / Setujui pengajuan RPP \'{{ addslashes($rpp->judul_rpp) }}\'?')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="terverifikasi">
                                                <button type="submit" title="Terima & Setujui RPP" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs inline-flex items-center gap-1 shadow-xs transition">
                                                    <i class="fas fa-check text-[10px]"></i> Terima
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[11px] border border-emerald-200 inline-flex items-center gap-1">
                                                <i class="fas fa-check-double text-[10px]"></i> Disetujui
                                            </span>
                                        @endif

                                        {{-- 2. Tombol Minta Revisi / Ubah Catatan Supervisi --}}
                                        <button onclick="openStatusModal({{ $rpp->id_rpp }}, 'perlu_revisi', '{{ addslashes($rpp->judul_rpp) }}')" title="Minta Revisi ke Guru" class="px-2 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 font-bold text-xs inline-flex items-center gap-1 transition">
                                            <i class="fas fa-undo text-[10px]"></i> Revisi
                                        </button>

                                        {{-- 3. Detail Modul --}}
                                        <button onclick="openViewModal({{ json_encode($rpp) }})" title="Lihat Detail Modul" class="w-7 h-7 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>

                                        {{-- 4. Download Berkas --}}
                                        @if($rpp->file_rpp)
                                            <a href="{{ route('rpp.download', $rpp->id_rpp) }}" title="Unduh Berkas RPP" class="w-7 h-7 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50 flex items-center justify-center transition" target="_blank">
                                                <i class="fas fa-file-pdf text-xs"></i>
                                            </a>
                                        @endif

                                        {{-- 5. Hapus RPP --}}
                                        <form action="{{ route('rpp.destroy', $rpp->id_rpp) }}" method="POST" class="inline-flex" onsubmit="return confirm('Yakin ingin menghapus RPP ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus RPP" class="w-7 h-7 rounded-lg border border-rose-200 text-rose-500 hover:bg-rose-50 flex items-center justify-center transition">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="p-8 text-center text-slate-400">Tidak ada data RPP ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200">
                    {{ $rppList->links() }}
                </div>
            </div>
        </div>

        <!-- Modal Ubah Status Verifikasi / Revisi -->
        <div id="statusModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-sm text-slate-900">Ubah Status Supervisi RPP</h3>
                    <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form id="formStatus" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PATCH')
                    <div>
                        <div class="font-semibold text-slate-500 mb-1">Judul RPP:</div>
                        <div id="statusJudulRpp" class="font-bold text-slate-900 bg-slate-50 p-2.5 rounded-lg border border-slate-200"></div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Supervisi <span class="text-rose-500">*</span></label>
                        <select name="status" id="selectStatus" required class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold">
                            <option value="draft">Draf Belum Lengkap</option>
                            <option value="menunggu_review">Menunggu Review</option>
                            <option value="terverifikasi">Terverifikasi (Disetujui)</option>
                            <option value="perlu_revisi">Perlu Revisi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Evaluasi / Revisi</label>
                        <textarea name="catatan" id="catatanRevisi" rows="3" placeholder="Tuliskan catatan perbaikan atau feedback untuk guru..."
                                  class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal View RPP Detail -->
        <div id="viewModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-sm text-slate-900">Detail Modul Ajar (RPP)</h3>
                    <button type="button" onclick="closeViewModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-500 font-semibold block">Topik Pembelajaran</span>
                        <span id="viewJudul" class="text-sm font-bold text-slate-900"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-slate-500 font-semibold block">Mata Pelajaran</span>
                            <span id="viewMapel" class="font-bold text-slate-800"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block">Guru Pengajar</span>
                            <span id="viewGuru" class="font-bold text-slate-800"></span>
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-500 font-semibold block">Deskripsi</span>
                        <p id="viewDeskripsi" class="text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100"></p>
                    </div>
                    <div id="viewFileWrap" class="hidden">
                        <span class="text-slate-500 font-semibold block mb-1">Berkas RPP</span>
                        <a id="viewFileLink" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold hover:bg-emerald-100 transition">
                            <i class="fas fa-file-pdf"></i> <span id="viewFileText">Unduh Berkas RPP</span>
                        </a>
                    </div>
                    <div>
                        <span class="text-slate-500 font-semibold block mb-1">Status Supervisi</span>
                        <div id="viewStatusBadge"></div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeViewModal()" class="px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-100">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    <script>
        function openStatusModal(id, status, judul) {
            document.getElementById('formStatus').action = `/rpp/${id}/status`;
            document.getElementById('statusJudulRpp').innerText = judul;
            document.getElementById('selectStatus').value = status;
            const m = document.getElementById('statusModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeStatusModal() {
            const m = document.getElementById('statusModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

function openViewModal(rpp) {
            document.getElementById('viewJudul').innerText = rpp.judul_rpp;
            document.getElementById('viewMapel').innerText = rpp.mata_pelajaran ? rpp.mata_pelajaran.nama_mapel : '-';
            document.getElementById('viewGuru').innerText = rpp.guru ? rpp.guru.nama_guru : '-';
            document.getElementById('viewDeskripsi').innerText = rpp.deskripsi || 'Tidak ada deskripsi.';

            const fileWrap = document.getElementById('viewFileWrap');
            const fileLink = document.getElementById('viewFileLink');
            const fileText = document.getElementById('viewFileText');
            if (rpp.file_rpp) {
                fileWrap.classList.remove('hidden');
                fileLink.href = '/rpp/' + rpp.id_rpp + '/download';
                fileText.innerText = rpp.file_rpp.split('/').pop();
            } else {
                fileWrap.classList.add('hidden');
            }

            const badge = document.getElementById('viewStatusBadge');
            if (rpp.status === 'terverifikasi') {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Terverifikasi</span>';
            } else if (rpp.status === 'menunggu_review') {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">Menunggu Review</span>';
            } else if (rpp.status === 'perlu_revisi') {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Perlu Revisi</span>';
            } else {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Draf Belum Lengkap</span>';
            }

            const m = document.getElementById('viewModal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeViewModal() {
            const m = document.getElementById('viewModal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
    </script>
@endsection
