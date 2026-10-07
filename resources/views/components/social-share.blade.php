@props([
    'url' => '',
    'text' => null,
    'label' => null,
])

<div class="flex flex-wrap items-center gap-2" x-data="{ copied: false }">
    @if ($label)
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $label }}</span>
    @endif

    @foreach (\App\Support\Social::shareLinks($url, $text) as $share)
        <a
            href="{{ $share['url'] }}"
            target="_blank"
            rel="noopener"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-white shadow-md transition-transform duration-200 hover:-translate-y-0.5"
            style="background-color: {{ $share['color'] }}"
            aria-label="Share on {{ $share['label'] }}"
            title="{{ $share['label'] }}"
        >
            <x-icon :name="$share['icon']" class="icon h-4 w-4" />
        </a>
    @endforeach

    <button
        type="button"
        class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-slate-200/80 bg-white/40 px-3 text-xs font-medium text-slate-600 backdrop-blur-md transition-colors hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10"
        @click="navigator.clipboard?.writeText('{{ $url }}'); copied = true; setTimeout(() => copied = false, 1500)"
        aria-label="Copy link"
    >
        <x-icon name="link" class="icon h-3.5 w-3.5" />
        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
    </button>
</div>
