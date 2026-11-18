{{-- Pagination baku Binaro — dipakai otomatis oleh $paginator->links() --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginasi" class="flex items-center justify-between flex-wrap gap-3 mt-4">
        <p class="text-[11px] text-slate-500">
            Menampilkan
            <span class="font-bold text-slate-700">{{ $paginator->firstItem() }}</span>–<span class="font-bold text-slate-700">{{ $paginator->lastItem() }}</span>
            dari <span class="font-bold text-slate-700">{{ $paginator->total() }}</span> data
        </p>

        <div class="flex items-center gap-1">
            {{-- Halaman Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3 py-1.5 text-[11px] font-bold text-slate-400 bg-surface-alt rounded-lg cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center px-3 py-1.5 text-[11px] font-bold text-slate-600 bg-white border border-line rounded-lg hover:border-primary hover:text-primary transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- "Tiga titik" pemisah --}}
                @if (is_string($element))
                    <span class="px-2 text-[11px] text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex items-center justify-center min-w-[30px] px-2.5 py-1.5 text-[11px] font-bold bg-primary text-white rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="inline-flex items-center justify-center min-w-[30px] px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 bg-white border border-line rounded-lg hover:border-primary hover:text-primary transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Halaman Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center px-3 py-1.5 text-[11px] font-bold text-slate-600 bg-white border border-line rounded-lg hover:border-primary hover:text-primary transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="inline-flex items-center px-3 py-1.5 text-[11px] font-bold text-slate-400 bg-surface-alt rounded-lg cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
