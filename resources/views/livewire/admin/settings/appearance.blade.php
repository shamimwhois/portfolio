<div>
    {{-- Theme Manager header --}}
    <section class="card card-pad flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Appearance</p>
            <h2 class="card-title mt-1 text-2xl">Theme Manager</h2>
            <p class="card-subtitle">
                Pick the visual identity for your public landing. The active theme drives the site-wide look, and
                each theme carries its own landing palette. Toggle sections and preview changes instantly.
            </p>
        </div>
        <a href="{{ route('home').'?preview_theme='.$this->theme_default }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
            <x-icon name="external" class="icon h-3.5 w-3.5" /> View site
        </a>

        <div class="grid w-full grid-cols-2 gap-3 sm:w-auto sm:grid-cols-4">
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value truncate text-sm text-brand-600 dark:text-brand-400">
                    <x-icon name="check" class="icon inline h-3.5 w-3.5" /> {{ $this->metrics['active_label'] }}
                </p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Active</p>
            </div>
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value text-slate-900 dark:text-white">{{ $this->metrics['themes'] }}</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Themes</p>
            </div>
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value text-slate-900 dark:text-white">{{ $this->metrics['sections_on'] }}<span class="text-sm font-medium text-slate-400">/{{ $this->metrics['sections_total'] }}</span></p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Sections</p>
            </div>
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value text-slate-900 dark:text-white">{{ $this->metrics['mode'] }}</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Mode</p>
            </div>
        </div>
    </section>

    {{-- Theme cards --}}
    <section class="mt-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Theme Library</p>
                <h3 class="card-title mt-1 text-lg">Pick a theme</h3>
            </div>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" wire:click="toggleSwitcher" @checked($theme_switcher) class="h-4 w-4 accent-brand-600" />
                Show theme switcher for visitors
            </label>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @php $meta = \App\Livewire\Admin\Settings\Appearance::THEME_META; @endphp
            @foreach (\App\Livewire\Admin\Settings\Appearance::THEMES as $index => $theme)
                @php
                    $isDark = str_starts_with($theme, 'dark');
                    $p = $paletteDefaults[$theme] ?? $paletteDefaults['light'];
                    $paper = $isDark ? '#0b0d17' : '#f4f5fb';
                    $line = $isDark ? 'rgba(220,231,255,0.4)' : 'rgba(30,41,59,0.28)';
                    $lineSoft = $isDark ? 'rgba(220,231,255,0.16)' : 'rgba(30,41,59,0.14)';
                    $cardBg = $isDark ? 'rgba(255,255,255,0.06)' : 'rgba(255,255,255,0.75)';
                    $border = $isDark ? 'rgba(255,255,255,0.1)' : 'rgba(30,41,59,0.08)';
                    $active = $theme === $this->theme_default;
                @endphp
                <article class="card card-pad card-hover flex flex-col overflow-hidden !p-0">
                    {{-- Mockup preview --}}
                    <div class="border-b border-white/50 p-4 dark:border-white/10" style="background: {{ $paper }}">
                        <div class="overflow-hidden rounded-xl ring-1 ring-inset ring-black/5 dark:ring-white/10" style="background: {{ $paper }}">
                            <div class="flex items-center gap-1.5 border-b px-3 py-2" style="border-color: {{ $border }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                <span class="ml-2 h-2 w-20 rounded-full" style="background: {{ $lineSoft }}"></span>
                            </div>
                            <div class="flex items-center justify-between px-4 pt-3">
                                <span class="h-2 w-9 rounded-full" style="background: {{ $line }}"></span>
                                <div class="flex gap-1.5">
                                    <span class="h-2 w-5 rounded-full" style="background: {{ $lineSoft }}"></span>
                                    <span class="h-2 w-5 rounded-full" style="background: {{ $lineSoft }}"></span>
                                    <span class="h-3.5 w-8 rounded-full" style="background: {{ $p['primary'] }}"></span>
                                </div>
                            </div>
                            <div class="space-y-1.5 px-4 pt-3">
                                <span class="block h-2.5 w-3/4 rounded-full" style="background: {{ $line }}"></span>
                                <span class="block h-2 w-1/2 rounded-full" style="background: {{ $lineSoft }}"></span>
                            </div>
                            <div class="flex gap-1.5 px-4 pt-2.5">
                                <span class="h-4 w-14 rounded-md" style="background: {{ $p['primary'] }}"></span>
                                <span class="h-4 w-14 rounded-md ring-1 ring-inset" style="background: {{ $p['secondary'] }}33; border-color: {{ $p['secondary'] }}"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-1.5 px-4 py-3">
                                @for ($i = 0; $i < 3; $i++)
                                    <span class="h-8 rounded-lg" style="background: {{ $cardBg }}"></span>
                                @endfor
                            </div>
                            <div class="px-4 pb-3 pt-1">
                                <span class="block h-1.5 w-1/3 rounded-full" style="background: {{ $lineSoft }}"></span>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isDark ? 'bg-slate-800 text-slate-200' : 'bg-amber-100 text-amber-700' }}">
                                {{ $isDark ? 'Dark' : 'Light' }}
                            </span>
                            @if ($active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                    <x-icon name="check" class="icon h-3 w-3" /> Active
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card body --}}
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Theme · #{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
                                <p class="truncate font-display text-base font-semibold text-slate-900 dark:text-white">{{ $meta[$theme]['label'] }}</p>
                            </div>
                            <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" style="background-color: {{ $p['primary'] }}1f; color: {{ $p['primary'] }}">
                                <span class="h-2 w-2 rounded-full" style="background: {{ $p['primary'] }}"></span>
                                {{ $meta[$theme]['description'] }}
                            </span>
                        </div>

                        <p class="mt-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ $meta[$theme]['description'] }} — a {{ $isDark ? 'dark' : 'light' }} paper band with its own
                            landing palette (primary {{ $p['primary'] }}).
                        </p>

                        <div class="mt-auto flex items-center gap-2 pt-4">
                            @if ($active)
                                <span class="btn btn-secondary btn-sm flex-1 cursor-default justify-center">
                                    <x-icon name="check" class="icon h-3.5 w-3.5" /> In Use
                                </span>
                            @else
                                <button type="button" wire:click="activate('{{ $theme }}')" wire:loading.attr="disabled" class="btn btn-primary btn-sm flex-1 justify-center">
                                    <x-icon name="check" class="icon h-3.5 w-3.5" /> Activate
                                </button>
                            @endif
                            <button
                                type="button"
                                wire:click="selectPaletteTheme('{{ $theme }}')"
                                @click="setTimeout(() => document.getElementById('landing-colours')?.scrollIntoView({ behavior: 'smooth' }), 350)"
                                class="btn btn-secondary btn-sm justify-center"
                                title="Edit this theme's landing palette"
                            >
                                <x-icon name="palette" class="icon h-3.5 w-3.5" /> Palette
                            </button>
                            <a href="{{ route('home').'?preview_theme='.$theme }}" target="_blank" rel="noopener" class="icon-btn shrink-0" title="Preview {{ $meta[$theme]['label'] }} on the site" aria-label="Preview {{ $meta[$theme]['label'] }} on the site">
                                <x-icon name="arrow-right" class="icon h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Landing Colours --}}
    <section id="landing-colours" class="card card-pad mt-8 scroll-mt-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Appearance</p>
                <h3 class="card-title mt-1 text-lg">Landing Colours</h3>
                <p class="card-subtitle">
                    Tune each theme's public landing palette. The active theme renders these colours across your
                    marketing pages — dashboards are unaffected.
                </p>
            </div>
            <span class="badge {{ $this->palette_theme === $this->theme_default ? 'badge-success' : 'badge-neutral' }}">
                <x-icon name="{{ $this->palette_theme === $this->theme_default ? 'check-circle' : 'palette' }}" class="icon h-3.5 w-3.5" />
                {{ $this->palette_theme === $this->theme_default ? 'Editing active theme' : 'Editing inactive theme' }}
            </span>
        </div>

        {{-- Theme tabs --}}
        <div class="mt-5 flex flex-wrap gap-2" role="tablist" aria-label="Palette theme">
            @foreach (\App\Livewire\Admin\Settings\Appearance::THEMES as $theme)
                <button
                    type="button"
                    role="tab"
                    wire:click="selectPaletteTheme('{{ $theme }}')"
                    aria-selected="{{ $this->palette_theme === $theme ? 'true' : 'false' }}"
                    class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-semibold transition-all duration-200 {{ $this->palette_theme === $theme ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'bg-white/40 text-slate-600 ring-1 ring-inset ring-slate-200/80 hover:bg-brand-500/10 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10' }}"
                >
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ ($paletteDefaults[$theme] ?? $paletteDefaults['light'])['primary'] }}"></span>
                    {{ $meta[$theme]['label'] }}
                    @if ($theme === $this->theme_default)
                        <x-icon name="check" class="icon h-3.5 w-3.5" />
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Colour inputs --}}
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['key' => 'primary', 'label' => 'Primary', 'hint' => 'Buttons, links & key accents'],
                ['key' => 'secondary', 'label' => 'Secondary', 'hint' => 'Secondary highlights & info'],
                ['key' => 'accent', 'label' => 'Accent', 'hint' => 'Success & positive accents'],
            ] as $slot)
                <div class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $slot['label'] }}</p>
                        <button
                            type="button"
                            class="icon-btn h-7 w-7"
                            wire:click="$set('palette.{{ $slot['key'] }}', '{{ ($paletteDefaults[$this->palette_theme] ?? $paletteDefaults['light'])[$slot['key']] }}')"
                            title="Reset {{ $slot['label'] }} to theme default"
                            aria-label="Reset {{ $slot['label'] }} colour"
                        >
                            <x-icon name="refresh" class="icon h-3.5 w-3.5" />
                        </button>
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        <label class="relative block h-12 w-12 shrink-0 cursor-pointer overflow-hidden rounded-xl shadow-inner ring-1 ring-inset ring-black/10 dark:ring-white/10" style="background-color: {{ $this->palette[$slot['key']] }}" title="Pick a colour">
                            <input
                                type="color"
                                name="palette_{{ $slot['key'] }}"
                                wire:model.live="palette.{{ $slot['key'] }}"
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                aria-label="{{ $slot['label'] }} colour picker"
                            />
                        </label>
                        <div class="min-w-0 flex-1">
                            <input
                                type="text"
                                name="palette_hex_{{ $slot['key'] }}"
                                wire:model.live.debounce.200ms="palette.{{ $slot['key'] }}"
                                class="input !py-2 font-mono text-sm uppercase"
                                placeholder="#RRGGBB"
                                aria-label="{{ $slot['label'] }} hex"
                            />
                            @error('palette.'.$slot['key']) <p class="error-text mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ $slot['hint'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Live preview --}}
        <div class="mt-5">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Live preview</p>
                <p class="text-xs text-slate-400">Updates as you edit</p>
            </div>
            <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-white/50 bg-white/40 px-5 py-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <button type="button" class="btn btn-sm px-5 text-white" style="background-color: {{ $this->palette['primary'] }}">Get Started</button>
                <a href="#" class="text-sm font-semibold underline-offset-4 hover:underline" style="color: {{ $this->palette['secondary'] }}" onclick="return false;">Learn more</a>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold text-white" style="background-color: {{ $this->palette['accent'] }}">
                    <x-icon name="check-circle" class="icon h-3.5 w-3.5" /> Verified
                </span>
            </div>
        </div>

        {{-- Palette footer --}}
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/60 pt-4 dark:border-white/10">
            <p class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <x-icon name="dot" class="icon h-3.5 w-3.5 text-brand-500" />
                Colours apply to the {{ $meta[$this->palette_theme]['label'] }} landing pages once saved.
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="resetPalette" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                    <x-icon name="refresh" class="icon h-3.5 w-3.5" /> Reset to defaults
                </button>
                <button type="button" wire:click="savePalette" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                    <x-icon name="check" class="icon h-3.5 w-3.5" /> Save {{ $meta[$this->palette_theme]['label'] }} palette
                </button>
            </div>
        </div>
    </section>

    {{-- Homepage sections --}}
    <section class="card card-pad mt-8">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Homepage</p>
                <h3 class="card-title mt-1 text-lg">Section visibility</h3>
                <p class="card-subtitle">Toggle which sections render on the homepage. Changes take effect immediately.</p>
            </div>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-4">
            @foreach (\App\Livewire\Admin\Settings\Appearance::SECTION_KEYS as $key => $label)
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-white/50 bg-white/40 px-3 py-2 text-sm text-slate-700 backdrop-blur-md dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                    <input type="checkbox" wire:click="toggleSection('{{ $key }}')" @checked($sections[$key] ?? false) class="h-4 w-4" />
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </section>

    {{-- Quickstart --}}
    <section class="card card-pad mt-8">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                <x-icon name="sparkle" class="icon h-5 w-5" />
            </span>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Quickstart</p>
                <h3 class="card-title text-base">How it works</h3>
            </div>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ([
                'Activate the theme you want — your choice is saved instantly and applies site-wide.',
                'Open Landing Colours and tune primary, secondary and accent. Saving re-tints buttons, links and badges across the public site.',
                'Use Section visibility to show or hide homepage sections without touching templates.',
                'Switching back is non-destructive: each theme keeps its own palette, so your data is preserved on both sides.',
            ] as $index => $step)
                <div class="flex items-start gap-3 rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 font-display text-xs font-bold text-white">
                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $step }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
