@extends(session('user_type') === 'siswa' ? 'layouts.siswa' : 'layouts.app')

@section('breadcrumb_trail')
    <a href="{{ route('siswa.data.index') }}" class="hover:text-slate-600 transition">Data Siswa</a>
    <i class="fas fa-chevron-right text-[8px] opacity-60"></i>
    <span class="text-slate-500 truncate">{{ $siswa->nm_siswa ?? 'Edit Siswa' }}</span>
@endsection

@section('page_title', 'Edit Data Siswa')
@section('page_subtitle', 'Ubah informasi siswa ' . ($siswa->nm_siswa ?? '') . '.')

@section('header_actions')
    <a href="{{ route('siswa.data.index') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-[#E6EBF2] hover:bg-slate-50 transition">
        <i class="fas fa-arrow-left text-[11px]"></i> Kembali
    </a>
@endsection

@section('content')
<div class="space-y-5 max-w-3xl mx-auto">
    <div class="card p-6">
        <form action="{{ route('siswa.data.update', $siswa->id_siswa) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('siswa.data._form')

            <div class="flex justify-end gap-2 pt-4 border-t border-[#E6EBF2]">
                <a href="{{ route('siswa.data.index') }}"
                   class="inline-flex items-center px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-[#E6EBF2] hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition"
                        style="background: var(--binaro-navy);">
                    <i class="fas fa-floppy-disk text-[11px]"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
