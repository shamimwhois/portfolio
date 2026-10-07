@if ($paginator->hasPages())
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-1.5" aria-label="Pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary btn-sm pointer-events-none opacity-40" aria-disabled="true">
                <x-icon name="arrow-left" class="icon h-3.5 w-3.5" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm" aria-label="Previous page">
                <x-icon name="arrow-left" class="icon h-3.5 w-3.5" />
            </a>
        @endif

        {{-- Elements --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="flex h-9 w-9 items-center justify-center text-sm font-medium text-slate-400 dark:text-slate-500" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 font-display text-sm font-bold text-white shadow-md shadow-brand-600/25"
                            aria-current="page"
                        >
                            {{ $page }}
                        </span>
                    @else
                        <a
                            href="{{ $url }}"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/50 bg-white/40 text-sm font-medium text-slate-600 backdrop-blur-md transition-all duration-200 hover:border-brand-400/50 hover:bg-brand-500/10 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-brand-400/40 dark:hover:bg-brand-400/10 dark:hover:text-brand-300"
                            aria-label="Go to page {{ $page }}"
                        >
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm" aria-label="Next page">
                <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
            </a>
        @else
            <span class="btn btn-secondary btn-sm pointer-events-none opacity-40" aria-disabled="true">
                <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
            </span>
        @endif
    </nav>
@endif
