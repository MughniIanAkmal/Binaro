@extends('layouts.app')

@section('page_title', 'QR Siswa')
@section('page_subtitle', 'Buat kartu QR code untuk absen. Guru memindai kartu ini saat mengabsen siswa.')

@section('content')
<div class="space-y-6">

    <!-- Progress QR -->
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center gap-4 flex-wrap">
        <div class="flex-1 min-w-[220px]">
            <div class="text-xs font-bold text-slate-900">{{ $sudah }} dari {{ $total }} siswa sudah punya QR code</div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden mt-2.5">
                <div class="h-full bg-primary rounded-full" style="width: {{ $total ? round($sudah / $total * 100) : 0 }}%"></div>
            </div>
        </div>
        <a href="{{ route('admin.qr.cetakKelas', request()->only('kelas')) }}" target="_blank"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 transition">
            Cetak semua kartu{{ request('kelas') ? ' kelas '.request('kelas') : '' }}
        </a>
        @if ($sudah < $total)
            <form method="POST" action="{{ route('admin.qr.storeAll') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-primary hover:bg-primary-hover text-white transition">
                    Buat semua yang belum punya
                </button>
            </form>
        @endif
    </section>

    <!-- Filter & Tabel QR -->
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <form class="p-4 border-b border-slate-200 flex gap-3 flex-wrap items-center" method="GET" action="{{ route('admin.qr.index') }}">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NISN" aria-label="Cari nama atau NISN"
                   class="flex-1 min-w-[160px] px-3 py-2 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none focus:border-primary">
            <select name="kelas" aria-label="Filter kelas" onchange="this.form.submit()"
                    class="px-3 py-2 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none focus:border-primary">
                <option value="">Semua kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k }}" @selected(request('kelas') === $k)>Kelas {{ $k }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 transition">
                Cari
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">Siswa</th>
                        <th class="p-3.5">Kelas</th>
                        <th class="p-3.5">QR code</th>
                        <th class="p-3.5 text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                @forelse ($siswas as $s)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 pl-6">
                            <div class="font-bold text-slate-900">{{ $s->nama_siswa }}</div>
                            <div class="text-[11px] text-slate-400">NISN: {{ $s->nisn }}</div>
                        </td>
                        <td class="p-3.5 font-medium text-slate-700">{{ $s->nama_kelas }}</td>
                        <td class="p-3.5">
                            @if ($s->barcode)
                                @php
                                    $qr = new \chillerlan\QRCode\QRCode();
                                    $svg = $qr->render($s->barcode->kode);
                                @endphp
                                <div class="w-12 h-12 bg-white border border-slate-200 rounded-lg p-1 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $svg }}" alt="QR {{ $s->nama_siswa }}" class="w-full h-full">
                                </div>
                                <div class="qr-inline hidden">
                                    <img src="{{ $svg }}" alt="QR {{ $s->nama_siswa }}">
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Belum dibuat</span>
                            @endif
                        </td>
                        <td class="p-3.5 pr-6">
                            <div class="flex items-center justify-end gap-2">
                            @if ($s->barcode)
                                <button class="card-open inline-flex items-center justify-center px-3 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 transition"
                                        type="button"
                                        data-nama="{{ $s->nama_siswa }}"
                                        data-kelas="{{ $s->nama_kelas }}"
                                        data-nisn="{{ $s->nisn }}"
                                        data-kode="{{ $s->barcode->kode }}"
                                        data-print="{{ route('admin.qr.cetak', $s) }}">
                                    Lihat kartu
                                </button>
                                <form method="POST" action="{{ route('admin.qr.regenerate', $s) }}" class="inline-block"
                                      onsubmit="return confirm('Buat ulang kode? Kartu lama tidak akan bisa dipakai lagi.')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-[11px] font-bold rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition">
                                        Buat ulang
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.qr.store', $s) }}" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-[11px] font-bold rounded-lg bg-primary hover:bg-primary-hover text-white transition">
                                        Buat QR code
                                    </button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-2"><x-empty-state icon="fa-qrcode" title="Tidak ada siswa yang cocok." message="Ubah kata kunci atau pilih kelas lain." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($siswas->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $siswas->links('pagination::default') }}
            </div>
        @endif
    </section>
</div>

<!-- Modal Kartu QR -->
<div id="cardModal" class="fixed inset-0 hidden items-center justify-center bg-slate-900/50 z-50 p-6" aria-hidden="true">
    <div class="w-[min(560px,92vw)] bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200" role="dialog" aria-modal="true" aria-labelledby="cardTitle">
        <div class="bg-amber-400 px-5 py-4 flex items-center justify-between">
            <div id="cardTitle" class="font-extrabold text-lg text-slate-900">{{ config('app.name') }}</div>
            <button type="button" class="text-3xl leading-none text-slate-900 hover:opacity-70 transition" aria-label="Tutup" data-close="cardModal">&times;</button>
        </div>
        <div class="p-5 bg-white">
            <div class="text-base font-extrabold text-slate-800">{{ config('app.name') }}</div>
            <div class="text-3xl font-extrabold leading-tight mt-4 text-slate-900 text-center" id="cardName">Nama Siswa</div>
            <div class="text-[15px] text-slate-500 text-center mt-1" id="cardMeta">Kelas ... | NISN ...</div>

            <div class="flex justify-center py-4 my-3 border-y-2 border-dashed border-slate-200">
                <div class="min-h-[220px] bg-white flex items-center justify-center" id="cardQr"></div>
            </div>

            <div class="text-xs text-slate-500 text-center mt-3">Kode unik (dipakai jika QR rusak)</div>
            <div class="text-[22px] font-extrabold tracking-wider text-center text-slate-900 mt-1" id="cardCode">SD-XXXXXXXXXX</div>

            <p class="text-sm text-slate-500 text-center leading-relaxed my-3">Setiap siswa punya kode unik. Guru cukup scan QR. Kode di bawahnya hanya dipakai manual jika QR rusak.</p>

            <div class="flex items-center justify-center gap-3 flex-wrap">
                <a id="cardPrintLink" href="#" target="_blank"
                   class="inline-flex items-center justify-center min-h-[42px] rounded-xl px-5 bg-primary hover:bg-primary-hover text-white text-[15px] font-bold transition">Cetak kartu</a>
                <button type="button" data-close="cardModal"
                        class="inline-flex items-center justify-center min-h-[42px] rounded-xl px-5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-[15px] font-bold transition">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('cardModal');
    const cardName = document.getElementById('cardName');
    const cardMeta = document.getElementById('cardMeta');
    const cardQr = document.getElementById('cardQr');
    const cardCode = document.getElementById('cardCode');
    const cardPrintLink = document.getElementById('cardPrintLink');

    function openCardModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeCardModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('.card-open').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const nama = btn.dataset.nama;
            const kelas = btn.dataset.kelas;
            const nisn = btn.dataset.nisn;
            const kode = btn.dataset.kode;
            const qrMarkup = btn.closest('tr').querySelector('.qr-inline')?.innerHTML || '';

            cardName.textContent = nama;
            cardMeta.textContent = 'Kelas ' + kelas + ' | NISN ' + nisn;
            cardQr.innerHTML = qrMarkup;
            cardQr.querySelector('img')?.classList.add('w-52', 'h-52', 'block');
            cardCode.textContent = kode;
            cardPrintLink.href = btn.dataset.print || '#';

            openCardModal();
        });
    });

    document.querySelectorAll('[data-close="cardModal"]').forEach(function (el) {
        el.addEventListener('click', function () {
            closeCardModal();
        });
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeCardModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeCardModal();
        }
    });
</script>
@endsection
