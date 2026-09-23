@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex justify-between items-start">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1">
                <span class="bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                    <i class="fas fa-check-circle text-[9px]"></i> Sinkronisasi Mesin Presensi & QR Siswa Aktif
                </span>
                <span class="text-slate-400">Dashboard &gt; Kelola Absensi Siswa</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rekap & Kelola Absensi Siswa</h2>
            <p class="text-xs text-slate-500">Monitoring dan rekapitulasi kehadiran harian seluruh rombel kelas SDN Kalitapen 01.</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Form Scan QR Cepat -->
            @if (session('user_type') === 'guru')
                <form action="{{ route('absensi.scan-qr') }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="kode_barcode" placeholder="Scan Barcode / Input ID..." required
                           class="px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white focus:outline-none focus:border-[#13527D]">
                    <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-emerald-700 flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-qrcode"></i> Scan QR
                    </button>
                </form>
            @endif
            <button onclick="openSettingsModal()" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-amber-700 flex items-center gap-2 shadow-sm">
                <i class="fas fa-cog text-xs"></i> Pengaturan Waktu
            </button>
            <button class="bg-[#0F2C59] text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-slate-900 flex items-center gap-2 shadow-sm">
                <i class="fas fa-lock text-xs"></i> Kunci & Kirim ke Dapodik
            </button>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start shadow-sm">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $kpi['persentase'] }}%</div>
                <div class="text-xs text-slate-600 font-semibold mt-1">Tingkat Kehadiran Hari Ini</div>
                <div class="text-[11px] text-emerald-600 font-bold mt-1"><i class="fas fa-arrow-up"></i> Target harian tercapai</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-chart-line"></i></div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start shadow-sm">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $kpi['hadir'] }} <span class="text-sm font-semibold text-slate-400">/ {{ $kpi['total'] }}</span></div>
                <div class="text-xs text-slate-600 font-semibold mt-1">Total Siswa Hadir</div>
                <div class="text-[11px] text-slate-400 mt-1">Target harian 100%</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="fas fa-user-check"></i></div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start shadow-sm">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $kpi['izin_sakit'] }} <span class="text-sm font-semibold text-slate-400">Siswa</span></div>
                <div class="text-xs text-slate-600 font-semibold mt-1">Izin & Sakit Hari Ini</div>
                <div class="text-[11px] text-amber-600 font-bold mt-1">Surat keterangan terlampir</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="fas fa-file-medical"></i></div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start shadow-sm">
            <div>
                <div class="text-2xl font-black text-rose-600">{{ $kpi['alpa'] }} <span class="text-sm font-semibold text-slate-400">Siswa</span></div>
                <div class="text-xs text-slate-600 font-semibold mt-1">Tanpa Keterangan (Alpa)</div>
                <div class="text-[11px] text-rose-500 font-bold mt-1">Perlu konfirmasi wali</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm"><i class="fas fa-user-times"></i></div>
        </div>
    </div>

    <!-- Filter & Table Presensi -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-4">
            <form action="{{ route('absensi.index') }}" method="GET" class="flex gap-3 items-center flex-wrap">
                <div>
                    <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()"
                           class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                </div>
                <div>
                    <select name="id_rooms" onchange="this.form.submit()"
                            class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id_rooms }}" {{ $selectedKelas == $k->id_rooms ? 'selected' : '' }}>{{ $k->pararel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" onchange="this.form.submit()"
                            class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="izin_sakit" {{ request('status') == 'izin_sakit' ? 'selected' : '' }}>Izin / Sakit</option>
                        <option value="Alpa" {{ request('status') == 'Alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">NO</th>
                        <th class="p-3.5">NAMA SISWA & NISN</th>
                        <th class="p-3.5">KELAS</th>
                        <th class="p-3.5">METODE & WAKTU</th>
                        <th class="p-3.5">STATUS</th>
                        <th class="p-3.5">KETERANGAN / SURAT</th>
                        <th class="p-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($siswaList as $i => $s)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 pl-6 text-slate-400 font-semibold">{{ $siswaList->firstItem() + $i }}</td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900">{{ $s->nm_siswa }}</div>
                            <div class="text-[11px] text-slate-400">NISN: {{ $s->nisn }}</div>
                        </td>
                        <td class="p-3.5 font-medium text-slate-700">{{ $s->nama_kelas ?? '-' }}</td>
                        <td class="p-3.5">
                            @if($s->id_absen && $s->metode == 'scan_qr')
                                <div class="font-bold text-slate-700"><i class="fas fa-qrcode text-emerald-600 mr-1"></i> Scan QR</div>
                                <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($s->waktu_absen)->format('H.i') }} WIB</div>
                            @elseif($s->id_absen && $s->metode == 'manual_guru')
                                <div class="font-bold text-slate-700"><i class="fas fa-user-edit text-blue-600 mr-1"></i> Form Guru</div>
                                <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($s->waktu_absen)->format('H.i') }} WIB</div>
                            @else
                                <span class="text-slate-400 italic">Belum Ada Presensi</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            @php $status = $s->id_absen ? $s->status_kehadiran : 'Alpa'; @endphp
                            @if($status == 'Hadir')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"><i class="fas fa-check text-[9px]"></i> Hadir</span>
                            @elseif($status == 'Sakit')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"><i class="fas fa-plus-circle text-[9px]"></i> Sakit</span>
                            @elseif($status == 'Izin')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800"><i class="fas fa-info-circle text-[9px]"></i> Izin</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800"><i class="fas fa-times text-[9px]"></i> Alpa</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            @if($s->id_absen && $s->status_kehadiran != 'Alpa')
                                <span class="font-medium text-slate-600">{{ $s->keterangan ?? '-' }}</span>
                                @if($s->berkas_surat)
                                    <a href="{{ asset('storage/' . $s->berkas_surat) }}" target="_blank" class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 hover:bg-sky-200 transition">
                                        <i class="fas fa-file-pdf"></i> Lihat Berkas
                                    </a>
                                @endif
                            @elseif($s->id_absen && $s->status_kehadiran == 'Alpa')
                                <span class="text-rose-500 font-medium">Belum Ada Konfirmasi</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            <button type="button"
                                    onclick="openEditModal({{ $s->id_siswa }}, '{{ addslashes($s->nm_siswa) }}', '{{ $status }}', '{{ addslashes($s->namaKeterangan ?? '') }}')"
                                    class="px-2.5 py-1 text-xs border border-slate-200 rounded-lg hover:bg-slate-100 font-semibold text-slate-700">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">Tidak ada data siswa ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 flex justify-between items-center text-xs text-slate-500">
            <div>Menampilkan {{ $siswaList->firstItem() ?? 0 }} - {{ $siswaList->lastItem() ?? 0 }} dari {{ $siswaList->total() }} siswa</div>
            <div>{{ $siswaList->links() }}</div>
        </div>
    </div>
</div>

@include('absensi.modal-edit')

<!-- Modal Settings -->
<div id="settingsModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200">
        <h3 class="font-bold text-sm mb-4">Pengaturan Waktu Absen</h3>
        <form action="{{ route('absensi.settings') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold">Jam Mulai Awal (Datang Lebih Awal)</label>
                <input type="time" name="batas_awal" value="{{ \App\Models\AbsensiSetting::get('batas_awal', '07:00') }}" class="w-full px-3 py-2 border rounded-lg text-xs">
            </div>
            <div>
                <label class="text-xs font-bold">Jam Tepat (Batas Hadir Tepat Waktu)</label>
                <input type="time" name="batas_tepat" value="{{ \App\Models\AbsensiSetting::get('batas_tepat', '08:00') }}" class="w-full px-3 py-2 border rounded-lg text-xs">
            </div>
            <div>
                <label class="text-xs font-bold">Jam Tutup (Sekolah Tutup / Batas Terlambat)</label>
                <input type="time" name="batas_tutup" value="{{ \App\Models\AbsensiSetting::get('batas_tutup', '12:00') }}" class="w-full px-3 py-2 border rounded-lg text-xs">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeSettingsModal()" class="px-4 py-2 border rounded-lg text-xs font-semibold text-slate-600">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(idSiswa, nama, status, keterangan) {
    document.getElementById('modalIdSiswa').value = idSiswa;
    document.getElementById('modalSiswaName').innerText = nama;
    document.getElementById('modalStatus').value = status;
    document.getElementById('modalKeterangan').value = keterangan;
    const modal = document.getElementById('editModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openSettingsModal() {
    const modal = document.getElementById('settingsModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeSettingsModal() {
    const modal = document.getElementById('settingsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
