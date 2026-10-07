@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null,
    'size' => 'md', // sm | md | lg | xl
    'closeMethod' => 'cancel',
])

@php
    $sizes = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-lg',
        'lg' => 'sm:max-w-2xl',
        'xl' => 'sm:max-w-4xl',
    ];
@endphp

<div
    x-data="adminModal"
    data-close-method="{{ $closeMethod }}"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="admin-modal-title"
>
    {{-- Backdrop --}}
    <div class="animate-fade-in absolute inset-0 bg-slate-900/30 backdrop-blur-md" @click="close()" aria-hidden="true"></div>

    {{-- Panel --}}
    <div class="glass-strong animate-modal-in relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl {{ $sizes[$size] ?? $sizes['md'] }}">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 border-b border-slate-200/60 px-5 py-4 dark:border-white/10">
            <div class="flex min-w-0 items-center gap-3">
                @if ($icon)
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-600 ring-1 ring-inset ring-brand-500/20 dark:bg-brand-400/10 dark:text-brand-300 dark:ring-brand-400/20">
                        <x-icon :name="$icon" class="icon h-5 w-5" />
                    </span>
                @endif
                <div class="min-w-0">
                    <h2 id="admin-modal-title" class="truncate font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h2>
                    @if ($subtitle)
                        <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <button type="button" class="icon-btn shrink-0" @click="close()" aria-label="Close dialog">
                <x-icon name="close" class="icon h-5 w-5" />
            </button>
        </div>

        {{-- Scrollable body --}}
        <div class="flex-1 overflow-y-auto px-5 py-5">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @isset($footer)
            <div class="shrink-0 border-t border-slate-200/60 px-5 py-4 dark:border-white/10">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
