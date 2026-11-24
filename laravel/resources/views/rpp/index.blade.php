@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'Persetujuan & Kelola RPP Guru')
@section('page_subtitle', 'Khusus meninjau & menyetujui pengajuan modul ajar dari guru.')

@section('content')
<!-- Content Body -->
        <div class="space-y-6">

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
                <div class="p-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('rpp.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
                        <a href="{{ route('rpp.index', ['status' => 'menunggu_review']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 {{ request('status') == 'menunggu_review' ? 'bg-primary text-white' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100' }}">
                            <i class="fas fa-inbox text-[11px] text-amber-500"></i>
                            <span>Permintaan Guru (Menunggu Review)</span>
                            @if(($kpi['perlu_review'] ?? 0) > 0)
                                <span class="bg-amber-400 text-slate-900 text-[10px] font-black px-1.5 py-0.2 rounded-full">{{ $kpi['perlu_review'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('rpp.index', ['status' => 'terverifikasi']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') == 'terverifikasi' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Terverifikasi (Disetujui)</a>
                        <a href="{{ route('rpp.index', ['status' => 'perlu_revisi']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') == 'perlu_revisi' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Perlu Revisi</a>
                        <a href="{{ route('rpp.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') == 'draft' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Draf Guru</a>
                    </div>

                    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition shadow-xs shrink-0 ml-auto">
                        <i class="fas fa-download text-[11px]"></i> Cetak / Export
                    </button>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                                <th class="p-3.5 pl-6">NO</th>
                                <th class="p-3.5">MATA PELAJARAN &amp; BAB</th>
                                <th class="p-3.5">KELAS &amp; FASE</th>
                                <th class="p-3.5">GURU PENGAJAR</th>
                                <th class="p-3.5 min-w-[170px]">KELENGKAPAN</th>
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
                                    <div class="text-[11px] text-slate-500">{{ $rpp->bab->nama_bab ?? 'Bab belum dipilih' }}</div>
                                    <div class="text-[10px] text-slate-400">Topik: {{ $rpp->judul_rpp }}</div>
                                </td>
                                <td class="p-3.5 font-medium text-slate-700">
                                    {{ $rpp->kelas->pararel ?? '-' }}
                                    @if($rpp->fase)
                                        <span class="block text-[10px] text-slate-400">{{ $rpp->fase }}</span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $rpp->guru->nama_guru ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">NIP. {{ $rpp->guru->nip ?? '-' }}</div>
                                </td>
                                <td class="p-3.5">
                                    @php
                                        $wajibKurang = $rpp->komponenWajibKurang();
                                        $disarankanTerisi = $rpp->jumlahDisarankanTerisi();
                                        $totalDisarankan = count(\App\Models\Rpp::KOMPONEN_DISARANKAN);
                                    @endphp
                                    <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                        @if($wajibKurang->isEmpty())
                                            <span class="text-emerald-700"><i class="fas fa-circle-check mr-1"></i>3/3 wajib</span>
                                        @else
                                            <span class="text-rose-600"><i class="fas fa-triangle-exclamation mr-1"></i>{{ 3 - $wajibKurang->count() }}/3 wajib</span>
                                        @endif
                                        <span class="text-slate-400">{{ $disarankanTerisi }}/{{ $totalDisarankan }} disarankan</span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full {{ $wajibKurang->isEmpty() ? 'bg-emerald-500' : 'bg-rose-500' }}"
                                             style="width: {{ $rpp->skorKelengkapan() }}%"></div>
                                    </div>
                                    @if($wajibKurang->isNotEmpty())
                                        <p class="text-[10px] text-rose-600 mt-1 font-semibold">
                                            {{ collect($wajibKurang)->map(fn ($k) => \App\Models\Rpp::KOMPONEN_WAJIB[$k])->first() }} belum lengkap
                                        </p>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    @if($rpp->status == 'terverifikasi')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"><i class="fas fa-check text-[10px]"></i> Terverifikasi</span>
                                    @elseif($rpp->status == 'menunggu_review')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800"><i class="fas fa-clock text-[10px]"></i> Menunggu Review</span>
                                    @elseif($rpp->status == 'perlu_revisi')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"><i class="fas fa-exclamation-triangle text-[10px]"></i> Perlu Revisi</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800"><i class="fas fa-times text-[10px]"></i> Draf Belum Lengkap</span>
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
                                        <button onclick='openStatusModal({{ $rpp->id_rpp }}, "perlu_revisi", @json($rpp->judul_rpp), @json($rpp->catatan->map(fn ($c) => ["isi" => $c->isi, "penulis_nama" => $c->penulis_nama, "created_at" => $c->created_at?->toIso8601String()])->values()))' title="Minta Revisi ke Guru" class="px-2 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 font-bold text-xs inline-flex items-center gap-1 transition">
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
                            <tr><td colspan="7" class="p-2"><x-empty-state icon="fa-inbox" title="Tidak ada data RPP ditemukan." /></td></tr>
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
                                  class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-primary"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Catatan disimpan sebagai riwayat dan tetap terlihat oleh guru, tidak menimpa catatan sebelumnya.</p>
                    </div>

                    {{-- Riwayat catatan supervisi sebelumnya (thread per versi). --}}
                    <div id="riwayatCatatanWrap" class="hidden">
                        <div class="font-bold text-slate-700 mb-1.5">Riwayat Catatan</div>
                        <div id="riwayatCatatan" class="space-y-2 max-h-48 overflow-y-auto pr-1"></div>
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
            <div class="bg-white rounded-2xl w-full max-w-3xl max-h-[90vh] my-8 overflow-hidden shadow-xl border border-slate-200 flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Detail Modul Ajar (RPP)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ config('binaro.nama_sekolah') }}</p>
                    </div>
                    <button type="button" onclick="closeViewModal()" class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 flex items-center justify-center transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4 pb-3 border-b border-slate-100">
                        <div><span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Topik / Judul</span><span id="viewJudul" class="text-sm font-bold text-slate-900"></span></div>
                        <div><span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Mata Pelajaran & Fase</span><span id="viewMapelFase" class="font-bold text-slate-800"></span></div>
                        <div><span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Guru Pengampu</span><span id="viewGuru" class="font-bold text-slate-800"></span></div>
                        <div><span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Target Jadwal</span><span id="viewJadwal" class="font-semibold text-slate-700"></span></div>
                    </div>
                    <div id="viewSessionDetail" class="hidden space-y-4"></div>
                    <div id="viewStatusRow" class="hidden p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Status Supervisi</span>
                        <div id="viewStatusBadge" class="mb-1"></div>
                        <div id="viewCatatanRevisi" class="hidden text-[11px] text-rose-800 bg-rose-50 border border-rose-200 rounded-lg p-3"></div>
                        <div id="viewFileWrap" class="hidden">
                            <a id="viewFileLink" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold hover:bg-emerald-100 transition">
                                <i class="fas fa-file-pdf"></i> <span id="viewFileText">Unduh Berkas RPP</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-slate-200 bg-slate-50 shrink-0">
                    <button type="button" onclick="closeViewModal()" class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 transition">Tutup</button>
                </div>
            </div>
        </div>

    <script>
        function openStatusModal(id, status, judul, catatan) {
            document.getElementById('formStatus').action = `/rpp/${id}/status`;
            document.getElementById('statusJudulRpp').innerText = judul;
            document.getElementById('selectStatus').value = status;
            document.getElementById('catatanRevisi').value = '';

            // Tampilkan riwayat catatan supervisi sebelumnya untuk dokumen ini.
            const wrap = document.getElementById('riwayatCatatanWrap');
            const list = document.getElementById('riwayatCatatan');
            const riwayat = Array.isArray(catatan) ? catatan : [];
            list.innerHTML = '';
            if (riwayat.length > 0) {
                riwayat.forEach((c) => {
                    const item = document.createElement('div');
                    item.className = 'rounded-lg bg-slate-50 border border-slate-200 p-2';
                    const nama = c.penulis_nama || 'Administrator';
                    const tanggal = c.created_at ? new Date(c.created_at).toLocaleDateString('id-ID') : '';
                    item.innerHTML = `<div class="text-[10px] font-bold text-slate-600">${nama} &middot; ${tanggal}</div>
                                      <div class="text-[11px] text-slate-700 mt-0.5"></div>`;
                    item.querySelector('div:last-child').textContent = c.isi;
                    list.appendChild(item);
                });
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }

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
            // Title
            document.getElementById('viewJudul').textContent = rpp.judul_rpp || '-';
            document.getElementById('viewMapelFase').textContent =
                `${rpp.mata_pelajaran?.nama_mapel || '-'} • ${rpp.fase || 'Fase B'} / ${rpp.kelas?.pararel || '-'}`;
            document.getElementById('viewGuru').textContent = rpp.guru?.nama_guru || '-';
            document.getElementById('viewJadwal').textContent = rpp.target_jadwal || '-';

            // Sessions detail
            const sessionDetail = document.getElementById('viewSessionDetail');
            if (rpp.sessions && rpp.sessions.length > 0) {
                sessionDetail.classList.remove('hidden');
                sessionDetail.innerHTML = '';
                rpp.sessions.forEach((session, index) => {
                    const konten = session.konten || {};
                    const card = document.createElement('div');
                    card.className = 'rounded-xl border border-slate-200 p-4 space-y-3';
                    card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-primary uppercase tracking-wider">Pertemuan ${session.nomor_pertemuan}</span>
                            <span class="text-[10px] text-slate-400">${Object.keys(konten).length > 0 ? 'Terisi' : 'Belum diisi'}</span>
                        </div>
                        ${konten.cp_text ? `<div class="p-3 rounded-lg bg-primary-tint border border-primary/10 text-xs leading-relaxed"><strong class="block text-[10px] font-bold text-primary mb-1">Capaian Pembelajaran</strong>${konten.cp_text}</div>` : ''}
                        ${konten.tp_list && konten.tp_list.length > 0 ? `
                            <div>
                                <strong class="block text-[10px] font-bold text-primary mb-1.5">Tujuan Pembelajaran (${konten.tp_list.length})</strong>
                                <ol class="list-decimal list-inside space-y-1 text-slate-700">
                                    ${konten.tp_list.map(tp => `<li>${tp.teks || '-'}${tp.dimensi?.length ? ` <span class="text-[10px] text-ink-soft">(${tp.dimensi.join(', ')})</span>` : ''}</li>`).join('')}
                                </ol>
                            </div>
                        ` : ''}
                        ${konten.langkah_pendahuluan || konten.langkah_penutup ? `
                            <div class="space-y-2">
                                ${konten.langkah_pendahuluan ? `<div class="p-3 rounded-lg bg-surface-alt border border-line"><strong class="block text-[10px] font-bold text-primary mb-1">Pendahuluan</strong><p class="text-slate-700">${konten.langkah_pendahuluan}</p></div>` : ''}
                                ${konten.langkah_inti && konten.langkah_inti.length > 0 ? `<div class="p-3 rounded-lg bg-surface-alt border border-line"><strong class="block text-[10px] font-bold text-primary mb-1">Kegiatan Inti</strong><ul class="list-disc list-inside text-slate-700 space-y-1 mt-1">${konten.langkah_inti.map(s => `<li>${s.uraian || s.nama || s}</li>`).join('')}</ul></div>` : ''}
                                ${konten.langkah_penutup ? `<div class="p-3 rounded-lg bg-surface-alt border border-line"><strong class="block text-[10px] font-bold text-primary mb-1">Penutup</strong><p class="text-slate-700">${konten.langkah_penutup}</p></div>` : ''}
                            </div>
                        ` : ''}
                    `;
                    sessionDetail.appendChild(card);
                });
            }

            // Status
            const statusRow = document.getElementById('viewStatusRow');
            statusRow.classList.remove('hidden');
            const badge = document.getElementById('viewStatusBadge');
            if (rpp.status === 'terverifikasi') {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5"><i class="fas fa-check-circle text-[10px]"></i> Terverifikasi</span>';
            } else if (rpp.status === 'menunggu_review') {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1.5"><i class="fas fa-clock text-[10px]"></i> Menunggu Review</span>';
            } else if (rpp.status === 'perlu_revisi') {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1.5"><i class="fas fa-exclamation-triangle text-[10px]"></i> Perlu Revisi</span>';
                const catatanRevisi = document.getElementById('viewCatatanRevisi');
                catatanRevisi.classList.remove('hidden');
                catatanRevisi.textContent = rpp.catatan_revisi || '-';
            } else {
                badge.innerHTML = '<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 inline-flex items-center gap-1.5"><i class="fas fa-file-lines text-[10px]"></i> Draf</span>';
            }

            // File download
            const fileWrap = document.getElementById('viewFileWrap');
            const fileLink = document.getElementById('viewFileLink');
            const fileText = document.getElementById('viewFileText');
            if (rpp.file_rpp) {
                fileWrap.classList.remove('hidden');
                fileLink.href = `/guru/rpp/${rpp.id_rpp}/download`;
                fileText.textContent = rpp.file_rpp.split('/').pop();
            } else {
                fileWrap.classList.add('hidden');
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
