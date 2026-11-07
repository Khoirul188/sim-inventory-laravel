@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center justify-end gap-1.5 flex-wrap">
        {{-- Tombol Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="Halaman sebelumnya" class="h-9 min-w-9 px-2.5 inline-flex items-center justify-center rounded-lg border border-line bg-slate-50 text-subtle text-sm cursor-not-allowed select-none">
                ‹
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="h-9 min-w-9 px-2.5 inline-flex items-center justify-center rounded-lg border border-line bg-white text-ink text-sm hover:bg-slate-50 transition-colors duration-150">
                ‹
            </a>
        @endif

        {{-- Elemen Nomor Halaman --}}
        @foreach ($elements as $element)
            {{-- Pemisah "..." --}}
            @if (is_string($element))
                <span aria-disabled="true" class="h-9 min-w-9 px-2 inline-flex items-center justify-center text-sm text-subtle select-none">
                    {{ $element }}
                </span>
            @endif

            {{-- Daftar Nomor Halaman --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="h-9 min-w-9 px-3 inline-flex items-center justify-center rounded-lg bg-primary-600 text-white text-sm font-medium tabular-nums">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="h-9 min-w-9 px-3 inline-flex items-center justify-center rounded-lg border border-line bg-white text-ink text-sm hover:bg-slate-50 transition-colors duration-150 tabular-nums">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Tombol Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="h-9 min-w-9 px-2.5 inline-flex items-center justify-center rounded-lg border border-line bg-white text-ink text-sm hover:bg-slate-50 transition-colors duration-150">
                ›
            </a>
        @else
            <span aria-disabled="true" aria-label="Halaman berikutnya" class="h-9 min-w-9 px-2.5 inline-flex items-center justify-center rounded-lg border border-line bg-slate-50 text-subtle text-sm cursor-not-allowed select-none">
                ›
            </span>
        @endif
    </nav>
@endif

