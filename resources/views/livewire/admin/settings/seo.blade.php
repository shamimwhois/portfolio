<div>
    {{-- Header --}}
    <section class="card card-pad flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">SEO &amp; Social</p>
            <h2 class="card-title mt-1 text-2xl">SEO Management</h2>
            <p class="card-subtitle">
                Global site defaults plus per-page overrides for every public route. Search engines follow the
                robots directive and meta written here on the live pages.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                <x-icon name="external" class="icon h-3.5 w-3.5" /> View site
            </a>
            <button type="button" wire:click="addPage" class="btn btn-primary btn-sm">
                <x-icon name="plus" class="icon h-3.5 w-3.5" /> Add SEO
            </button>
        </div>
    </section>

    {{-- Stat strip --}}
    <section class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card card-pad">
            <p class="stat-label">Total</p>
            <p class="stat-value mt-1 text-brand-600 dark:text-brand-400">{{ $this->metrics['total'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">SEO records managed</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">With meta</p>
            <p class="stat-value mt-1 text-emerald-600 dark:text-emerald-400">{{ $this->metrics['optimized'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Have a title or description</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Noindex</p>
            <p class="stat-value mt-1 text-amber-600 dark:text-amber-400">{{ $this->metrics['noindex'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Hidden from search engines</p>
        </div>
    </section>

    {{-- Records table --}}
    <section class="card card-pad mt-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="card-title">SEO Records</h3>
                <p class="card-subtitle">Every public page inherits Global SEO unless it has its own record.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="badge badge-neutral">{{ count($this->filteredRows) }} {{ count($this->filteredRows) === 1 ? 'record' : 'records' }}</span>
                <div class="relative">
                    <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="seo_search" wire:model.live.debounce.250ms="search" class="input pl-10" placeholder="Search pages..." aria-label="Search SEO records" />
                </div>
            </div>
        </div>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-white/50 bg-white/40 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200/60 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:border-white/10">
                        <th class="px-4 py-3">Page name</th>
                        <th class="px-4 py-3">Meta title</th>
                        <th class="px-4 py-3">Robots</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3">Updated at</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 dark:divide-white/10">
                    @forelse ($this->filteredRows as $key => $row)
                        <tr class="transition-colors hover:bg-white/40 dark:hover:bg-white/5">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                                        <x-icon name="{{ $row['kind'] === 'global' ? 'globe' : ($row['kind'] === 'custom' ? 'link' : 'doc') }}" class="icon h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-2 font-display text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ $row['label'] }}
                                            @if ($row['kind'] === 'global')
                                                <span class="badge badge-brand !px-2 !py-0.5 text-[9px]">Primary</span>
                                            @elseif ($row['kind'] === 'custom')
                                                <span class="badge badge-neutral !px-2 !py-0.5 text-[9px]">Custom</span>
                                            @endif
                                        </p>
                                        <p class="truncate text-xs text-slate-400">{{ $row['description'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="max-w-[260px] px-4 py-3.5">
                                <p class="truncate text-xs text-slate-600 dark:text-slate-300">{{ $row['meta_title'] ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                @php
                                    $robots = $row['robots'];
                                    $noindex = filled($robots) && str_starts_with($robots, 'noindex');
                                    $custom = filled($robots) && $robots !== 'index,follow';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $noindex ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : ($custom ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400') }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $noindex ? 'bg-amber-500' : ($custom ? 'bg-sky-500' : 'bg-emerald-500') }}"></span>
                                    {{ filled($robots) ? strtoupper($robots) : 'DEFAULT' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if (filled($row['og_image']))
                                    <img src="{{ $row['og_image'] }}" alt="" class="h-9 w-9 rounded-lg object-cover ring-1 ring-inset ring-black/10 dark:ring-white/10" />
                                @else
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-300 ring-1 ring-inset ring-black/5 dark:bg-white/5 dark:text-slate-600 dark:ring-white/10">
                                        <x-icon name="image" class="icon h-4 w-4" />
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                                {{ filled($row['updated_at']) ? \Carbon\Carbon::parse($row['updated_at'])->format('d M, Y') : 'Not set' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($row['kind'] === 'custom')
                                        <button type="button" wire:click="deletePage('{{ $key }}')" class="icon-btn h-8 w-8 text-red-500" title="Delete record" aria-label="Delete {{ $row['label'] }}">
                                            <x-icon name="trash" class="icon h-3.5 w-3.5" />
                                        </button>
                                    @endif
                                    <button type="button" wire:click="manage('{{ $key }}')" class="btn btn-secondary btn-sm">
                                        <x-icon name="settings" class="icon h-3.5 w-3.5" /> Manage SEO
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                No SEO records match your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Manage / Add SEO modal --}}
    @if ($editingKey || $isNew)
        <div
            class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="seo-modal-title"
            data-close-method="closeModal"
            x-data="adminModal"
            @keydown.escape.window="close()"
        >
            <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-md" wire:click="closeModal" aria-hidden="true"></div>

            <div class="glass-strong animate-modal-in relative w-full overflow-hidden rounded-t-2xl sm:max-w-xl sm:rounded-2xl">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200/60 px-5 py-4 dark:border-white/10">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500/15 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                            <x-icon name="{{ $isNew ? 'plus' : 'settings' }}" class="icon h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 id="seo-modal-title" class="truncate font-display text-lg font-semibold text-slate-900 dark:text-white">
                                {{ $isNew ? 'Add SEO record' : 'Manage SEO' }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                @if ($isNew)
                                    Register a custom path
                                @elseif ($editingKey === 'global')
                                    Global SEO · site-wide defaults
                                @else
                                    {{ \App\Support\Seo::rows()[$editingKey]['label'] ?? ucfirst($editingKey) }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" class="icon-btn shrink-0" wire:click="closeModal" aria-label="Close">
                        <x-icon name="close" class="icon h-5 w-5" />
                    </button>
                </div>

                <div class="max-h-[70vh] space-y-4 overflow-y-auto px-5 py-5">
                    @if ($isNew)
                        <div>
                            <label class="label">Page path</label>
                            <input type="text" name="seo_edit_path" wire:model="editPath" class="input" placeholder="/custom-page" />
                            @error('editPath') <p class="error-text mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="label">Meta title <span class="text-slate-400">(max 160)</span></label>
                        <input type="text" name="seo_edit_title" wire:model="editTitle" class="input" maxlength="160" placeholder="Page title shown in search results" />
                        @error('editTitle') <p class="error-text mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Meta description <span class="text-slate-400">(max 320)</span></label>
                        <textarea name="seo_edit_description" wire:model="editDescription" rows="3" class="input resize-none" maxlength="320" placeholder="Short summary for search snippets"></textarea>
                        @error('editDescription') <p class="error-text mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label">Robots</label>
                            <select name="seo_edit_robots" wire:model="editRobots" class="input">
                                @foreach (\App\Support\Seo::ROBOTS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('editRobots') <p class="error-text mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Open Graph image URL</label>
                            <input type="text" name="seo_edit_og_image" wire:model="editOgImage" class="input" placeholder="https://…/og.png" />
                            @error('editOgImage') <p class="error-text mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-slate-200/60 px-5 py-4 dark:border-white/10">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary btn-sm">Cancel</button>
                    <button type="button" wire:click="savePage" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                        <x-icon name="check-circle" class="icon h-3.5 w-3.5" /> Save SEO
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
