@extends(session('user_type') === 'admin' ? 'layouts.app' : 'layouts.guru')

@section('content')
<div class="p-8 space-y-6">
    <!-- Header Title & Action Buttons -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1">
                @if(session('user_type') === 'guru')
                <span class="bg-sky-100 text-[#13527D] px-2.5 py-0.5 rounded-full flex items-center gap-1 font-bold">
                    <i class="fas fa-chalkboard-user text-[9px]"></i> Portal Guru
                </span>
                <span class="text-slate-400">Portal Guru &gt; Rekapitulasi Absensi</span>
                @else
                <span class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                    <i class="fas fa-shield-alt text-[9px]"></i> Panel Administrator
                </span>
                <span class="text-slate-400">Dashboard &gt; Rekapitulasi Absensi</span>
                @endif
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rekapitulasi Absensi Siswa</h2>
            <p class="text-xs text-slate-500">Laporan presensi harian siswa SDN Kalitapen 01 terintegrasi QR Code & Form Manual Guru.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-emerald-700 flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button onclick="window.print()" class="bg-rose-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-rose-700 flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- 6 KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Card 1: Total Siswa -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Total Siswa</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $summary['total_siswa'] ?? 0 }}</div>
            <div class="text-[10px] text-slate-400 mt-1">Terdaftar di sistem</div>
        </div>

        <!-- Card 2: Hadir -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between border-l-4 border-l-emerald-500">
            <div class="text-[11px] font-bold text-emerald-700 uppercase">Hadir</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $summary['hadir'] ?? 0 }}</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-1"><i class="fas fa-check-circle"></i> Presensi tercatat</div>
        </div>

        <!-- Card 3: Izin -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between border-l-4 border-l-sky-500">
            <div class="text-[11px] font-bold text-sky-700 uppercase">Izin</div>
            <div class="text-2xl font-black text-sky-600 mt-1">{{ $summary['izin'] ?? 0 }}</div>
            <div class="text-[10px] text-sky-600 font-semibold mt-1"><i class="fas fa-info-circle"></i> Keterangan izin</div>
        </div>

        <!-- Card 4: Sakit -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between border-l-4 border-l-amber-500">
            <div class="text-[11px] font-bold text-amber-700 uppercase">Sakit</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $summary['sakit'] ?? 0 }}</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-1"><i class="fas fa-plus-circle"></i> Surat keterangan</div>
        </div>

        <!-- Card 5: Alpa -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between border-l-4 border-l-rose-500">
            <div class="text-[11px] font-bold text-rose-700 uppercase">Alpa</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $summary['alpa'] ?? 0 }}</div>
            <div class="text-[10px] text-rose-500 font-bold mt-1"><i class="fas fa-exclamation-triangle"></i> Tanpa keterangan</div>
        </div>

        <!-- Card 6: % Hadir -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between border-l-4 border-l-blue-600">
            <div class="text-[11px] font-bold text-blue-700 uppercase">% Hadir</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $summary['persentase'] ?? $summary['persentase_hadir'] ?? 0 }}%</div>
            <div class="text-[10px] text-blue-600 font-semibold mt-1">Tingkat kehadiran</div>
        </div>
    </div>

    <!-- Filter Bar & Table Presensi -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-4">
            <form action="{{ url()->current() }}" method="GET" class="flex gap-3 items-center flex-wrap w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600">Tanggal:</label>
                    <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()"
                           class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600">Kelas:</label>
                    <select name="id_rooms" onchange="this.form.submit()"
                            class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id_rooms }}" {{ $selectedKelas == $k->id_rooms ? 'selected' : '' }}>{{ $k->pararel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-600">Status:</label>
                    <select name="status" onchange="this.form.submit()"
                            class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="Hadir" {{ $selectedStatus == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="Izin" {{ $selectedStatus == 'Izin' ? 'selected' : '' }}>Izin</option>
                        <option value="Sakit" {{ $selectedStatus == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="Alpa" {{ $selectedStatus == 'Alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Table Presensi -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">NO</th>
                        <th class="p-3.5">SISWA</th>
                        <th class="p-3.5">NISN</th>
                        <th class="p-3.5">KELAS</th>
                        <th class="p-3.5">STATUS</th>
                        <th class="p-3.5">JAM MASUK</th>
                        <th class="p-3.5">GURU PENCATAT</th>
                        <th class="p-3.5">METODE</th>
                        <th class="p-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($siswaList as $i => $s)
                    @php
                        $status = $s->status ?? $s->status_kehadiran ?? 'Alpa';
                        $isHighAlpa = in_array($s->id_siswa, $alpaAlertSiswaIds ?? []);
                        $isTerlambat = $status === 'Hadir' && isset($s->keterangan) && str_contains((string) $s->keterangan, 'Terlambat');
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ $isHighAlpa ? 'bg-rose-50/40' : '' }}">
                        <td class="p-3.5 pl-6 text-slate-400 font-semibold">{{ $siswaList->firstItem() + $i }}</td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span>{{ $s->nm_siswa }}</span>
                                @if($isHighAlpa)
                                    <span class="bg-[#EF4444] text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full shadow-sm">
                                        <i class="fas fa-exclamation-triangle"></i> Alpa &gt; 2 Hari
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3.5 font-mono text-slate-600">{{ $s->nisn }}</td>
                        <td class="p-3.5 font-medium text-slate-700">{{ $s->nama_kelas ?? $s->kelas->pararel ?? '-' }}</td>
                        <td class="p-3.5">
                            @if($isTerlambat)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"><i class="fas fa-clock text-[9px]"></i> Terlambat</span>
                            @elseif($status == 'Hadir')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"><i class="fas fa-check text-[9px]"></i> Hadir</span>
                            @elseif($status == 'Sakit')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"><i class="fas fa-plus-circle text-[9px]"></i> Sakit</span>
                            @elseif($status == 'Izin')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800"><i class="fas fa-info-circle text-[9px]"></i> Izin</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800"><i class="fas fa-times text-[9px]"></i> Alpa</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-slate-600">
                            @if($s->waktu_absen)
                                {{ \Carbon\Carbon::parse($s->waktu_absen)->format('H:i:s') }} WIB
                            @else
                                <span class="text-slate-400 italic">-</span>
                            @endif
                        </td>
                        <td class="p-3.5 font-medium text-slate-700">
                            {{ $s->nama_guru_pencatat ?? $s->guru->nama_guru ?? 'Sistem / Guru Piket' }}
                        </td>
                        <td class="p-3.5">
                            @if(($s->metode ?? '') == 'scan_qr')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded text-[10px]">
                                    <i class="fas fa-qrcode"></i> scan_qr
                                </span>
                            @elseif(($s->metode ?? '') == 'manual_guru' || ($s->metode ?? '') == 'manual')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 font-bold rounded text-[10px]">
                                    <i class="fas fa-user-edit"></i> manual
                                </span>
                            @else
                                <span class="text-slate-400 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            <button type="button"
                                    onclick="openDetailModal({{ json_encode([
                                        'nama' => $s->nm_siswa,
                                        'nisn' => $s->nisn,
                                        'kelas' => $s->nama_kelas ?? '-',
                                        'status' => $isTerlambat ? 'Terlambat' : $status,
                                        'jam' => $s->waktu_absen ? \Carbon\Carbon::parse($s->waktu_absen)->format('H:i:s') . ' WIB' : '-',
                                        'guru' => $s->nama_guru_pencatat ?? 'Sistem / Guru Piket',
                                        'metode' => $s->metode ?? '-'
                                    ]) }})"
                                    class="px-2.5 py-1 text-xs border border-slate-200 rounded-lg hover:bg-slate-100 font-semibold text-slate-700 transition">
                                <i class="fas fa-eye mr-1"></i> Lihat Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400">Tidak ada data absensi ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 flex justify-between items-center text-xs text-slate-500">
            <div>Menampilkan {{ $siswaList->firstItem() ?? 0 }} - {{ $siswaList->lastItem() ?? 0 }} dari {{ $siswaList->total() }} record</div>
            <div>{{ $siswaList->links() }}</div>
        </div>
    </div>
</div>

<!-- Modal Detail Absensi -->
<div id="detailModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                <i class="fas fa-id-card text-blue-600"></i> Detail Absensi Siswa
            </h3>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="space-y-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 space-y-1">
                <div class="font-black text-sm text-slate-900" id="detailNama"></div>
                <div class="text-slate-500">NISN: <span id="detailNisn" class="font-mono text-slate-700"></span></div>
                <div class="text-slate-500">Kelas: <span id="detailKelas" class="font-bold text-slate-700"></span></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px]">STATUS KEHADIRAN</span>
                    <span id="detailStatus" class="font-bold text-slate-900 mt-0.5 block"></span>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px]">JAM MASUK</span>
                    <span id="detailJam" class="font-bold text-slate-900 mt-0.5 block"></span>
                </div>
            </div>

            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                <span class="text-slate-400 font-semibold block text-[10px]">GURU PENCATAT / PETUGAS</span>
                <span id="detailGuru" class="font-bold text-slate-900 mt-0.5 block"></span>
            </div>

            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                <span class="text-slate-400 font-semibold block text-[10px]">METODE ABSENSI</span>
                <span id="detailMetode" class="font-bold text-slate-900 mt-0.5 block"></span>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-slate-800 text-white rounded-lg font-bold hover:bg-slate-900">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openDetailModal(data) {
    document.getElementById('detailNama').innerText = data.nama;
    document.getElementById('detailNisn').innerText = data.nisn;
    document.getElementById('detailKelas').innerText = data.kelas;
    document.getElementById('detailStatus').innerText = data.status;
    document.getElementById('detailJam').innerText = data.jam;
    document.getElementById('detailGuru').innerText = data.guru;
    document.getElementById('detailMetode').innerText = data.metode;

    const modal = document.getElementById('detailModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
