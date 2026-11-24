@extends('layouts.app')

@section('page_title', 'Master Capaian Pembelajaran')
@section('page_subtitle', 'Kelola referensi CP per mata pelajaran dan fase untuk digunakan guru pada Modul Ajar.')

@section('content')
<div class="max-w-6xl mx-auto space-y-5">
    <section class="card p-5 sm:p-6">
        <h2 class="text-base font-bold text-ink">Tambah Capaian Pembelajaran</h2>
        <p class="text-xs text-ink-soft mt-1">Masukkan CP yang telah diverifikasi sekolah. CP akan tampil sebagai referensi baca-saja bagi guru.</p>
        <form action="{{ route('rpp.cp.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
            @csrf
            <div><label class="block text-xs font-semibold mb-1.5" for="id_mapel">Mata Pelajaran</label><select id="id_mapel" name="id_mapel" required class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="">Pilih mata pelajaran</option>@foreach($mapelList as $mapel)<option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-semibold mb-1.5" for="fase">Fase</label><select id="fase" name="fase" required class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="A">Fase A (Kelas 1–3)</option><option value="B">Fase B (Kelas 4–6)</option></select></div>
            <div><label class="block text-xs font-semibold mb-1.5" for="elemen">Elemen (opsional)</label><input id="elemen" name="elemen" maxlength="150" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
            <div><label class="block text-xs font-semibold mb-1.5" for="urutan">Urutan</label><input id="urutan" name="urutan" type="number" min="0" value="0" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
            <div class="md:col-span-2"><label class="block text-xs font-semibold mb-1.5" for="cp_text">Teks CP</label><textarea id="cp_text" name="cp_text" rows="4" required maxlength="10000" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></textarea></div>
            <div class="md:col-span-2 flex justify-end"><button class="px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-hover"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Simpan CP</button></div>
        </form>
    </section>

    <section class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-line flex items-center justify-between"><div><h2 class="text-sm font-bold">Daftar CP</h2><p class="text-xs text-ink-soft">{{ $cpList->total() }} referensi</p></div><a href="{{ route('rpp.index') }}" class="text-xs font-semibold text-primary hover:underline">Kembali ke tinjauan RPP</a></div>
        <div class="divide-y divide-line">
            @forelse($cpList as $cp)
            <details class="p-5">
                <summary class="cursor-pointer flex flex-wrap items-center gap-2 justify-between text-sm"><span class="font-bold">{{ $cp->mataPelajaran->nama_mapel ?? 'Mapel dihapus' }} · Fase {{ $cp->fase }}{{ $cp->elemen ? ' · '.$cp->elemen : '' }}</span><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $cp->is_active ? 'bg-success-tint text-success' : 'bg-surface-alt text-ink-soft' }}">{{ $cp->is_active ? 'Aktif' : 'Nonaktif' }}</span></summary>
                <form action="{{ route('rpp.cp.update', $cp->id_cp) }}" method="POST" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                    @csrf @method('PUT')
                    <div><label class="block text-xs font-semibold mb-1">Mata Pelajaran</label><select name="id_mapel" required class="w-full px-3 py-2 rounded-xl bg-surface-alt border border-line text-sm">@foreach($mapelList as $mapel)<option value="{{ $mapel->id_mapel }}" @selected($cp->id_mapel === $mapel->id_mapel)>{{ $mapel->nama_mapel }}</option>@endforeach</select></div>
                    <div><label class="block text-xs font-semibold mb-1">Fase</label><select name="fase" class="w-full px-3 py-2 rounded-xl bg-surface-alt border border-line text-sm"><option value="A" @selected($cp->fase === 'A')>Fase A</option><option value="B" @selected($cp->fase === 'B')>Fase B</option></select></div>
                    <div><label class="block text-xs font-semibold mb-1">Elemen</label><input name="elemen" value="{{ $cp->elemen }}" class="w-full px-3 py-2 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div><label class="block text-xs font-semibold mb-1">Urutan</label><input name="urutan" type="number" min="0" value="{{ $cp->urutan }}" class="w-full px-3 py-2 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-semibold mb-1">Teks CP</label><textarea name="cp_text" rows="4" required class="w-full px-3 py-2 rounded-xl bg-surface-alt border border-line text-sm">{{ $cp->cp_text }}</textarea></div>
                    <label class="flex items-center gap-2 text-xs font-semibold"><input type="checkbox" name="is_active" value="1" @checked($cp->is_active) class="rounded border-line text-primary">CP aktif dan dapat dipilih guru</label>
                    <div class="flex justify-end"><button class="px-4 py-2 rounded-xl border border-line bg-white text-xs font-bold hover:bg-surface-alt">Simpan Perubahan</button></div>
                </form>
            </details>
            @empty
            <div class="p-10 text-center"><i class="fas fa-book-open text-2xl text-ink-muted" aria-hidden="true"></i><p class="mt-3 font-bold text-sm">Master CP belum diisi</p><p class="text-xs text-ink-soft mt-1">Tambahkan CP resmi yang sudah diverifikasi agar tersedia di wizard guru.</p></div>
            @endforelse
        </div>
        <div class="p-4 border-t border-line">{{ $cpList->links() }}</div>
    </section>
</div>
@endsection
