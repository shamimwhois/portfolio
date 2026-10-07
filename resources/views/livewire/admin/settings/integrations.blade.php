<div>
    {{-- Integration workspace banner --}}
    <section class="card card-pad flex flex-wrap items-center justify-between gap-6">
        <div class="flex min-w-0 items-center gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-500/10 text-violet-700 ring-1 ring-inset ring-violet-400/30 dark:text-violet-300 dark:ring-violet-400/20">
                <x-icon name="puzzle" class="icon h-6 w-6" />
            </span>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Integration Workspace</p>
                <h2 class="card-title mt-1 text-2xl">Integration Center</h2>
                <p class="card-subtitle">
                    Configure plugin credentials, status, and operational controls from one grouped integration
                    surface. Every card reflects the live configuration of its settings page.
                </p>
            </div>
        </div>

        <div class="grid w-full grid-cols-2 gap-3 sm:w-auto sm:grid-cols-4">
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value text-brand-600 dark:text-brand-400">{{ $this->metrics['total'] }}</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total</p>
            </div>
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value text-slate-900 dark:text-white">{{ $this->metrics['groups'] }}</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Groups</p>
            </div>
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value text-emerald-600 dark:text-emerald-400">{{ $this->metrics['active'] }}</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Active</p>
            </div>
            <div class="rounded-2xl border border-white/50 bg-white/40 px-4 py-3 text-center backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <p class="stat-value text-slate-900 dark:text-white">{{ $this->metrics['inactive'] }}</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Inactive</p>
            </div>
        </div>
    </section>

    {{-- Registry header --}}
    <section class="mt-8 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Available Integrations</p>
            <h3 class="card-title mt-1 text-lg">Plugin Registry</h3>
        </div>
        <span class="badge badge-neutral">{{ $this->metrics['total'] }} plugins</span>
    </section>

    {{-- Group tabs --}}
    <section class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5" role="tablist" aria-label="Integration groups">
        <button
            type="button"
            role="tab"
            wire:click="$set('group', 'all')"
            aria-selected="{{ $this->group === 'all' ? 'true' : 'false' }}"
            class="flex items-center gap-3 rounded-2xl border p-4 text-left transition-all duration-200 {{ $this->group === 'all' ? 'border-violet-400/50 bg-violet-500/10 ring-1 ring-inset ring-violet-400/30' : 'border-white/50 bg-white/40 backdrop-blur-md hover:border-brand-300/50 hover:bg-brand-500/5 dark:border-white/10 dark:bg-white/5' }}"
        >
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-600 ring-1 ring-inset ring-violet-400/30 dark:text-violet-300">
                <x-icon name="plug" class="icon h-4 w-4" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold {{ $this->group === 'all' ? 'text-violet-700 dark:text-violet-300' : 'text-slate-900 dark:text-white' }}">All Integrations</span>
                <span class="mt-0.5 block text-xs {{ $this->group === 'all' ? 'text-violet-600/80 dark:text-violet-300/80' : 'text-slate-500 dark:text-slate-400' }}">
                    {{ $this->metrics['total'] }} plugins · {{ $this->metrics['active'] }} active
                </span>
            </span>
        </button>

        @foreach ($this->groups as $gkey => $group)
            <button
                type="button"
                role="tab"
                wire:click="$set('group', '{{ $gkey }}')"
                aria-selected="{{ $this->group === $gkey ? 'true' : 'false' }}"
                class="flex items-center gap-3 rounded-2xl border p-4 text-left transition-all duration-200 {{ $this->group === $gkey ? 'border-violet-400/50 bg-violet-500/10 ring-1 ring-inset ring-violet-400/30' : 'border-white/50 bg-white/40 backdrop-blur-md hover:border-brand-300/50 hover:bg-brand-500/5 dark:border-white/10 dark:bg-white/5' }}"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $group['color'] }}1f; color: {{ $group['color'] }}">
                    <x-icon :name="$group['icon']" class="icon h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold {{ $this->group === $gkey ? 'text-violet-700 dark:text-violet-300' : 'text-slate-900 dark:text-white' }}">{{ $group['label'] }}</span>
                    <span class="mt-0.5 block text-xs {{ $this->group === $gkey ? 'text-violet-600/80 dark:text-violet-300/80' : 'text-slate-500 dark:text-slate-400' }}">
                        {{ $this->groupStats[$gkey]['count'] }} plugins · {{ $this->groupStats[$gkey]['active'] }} active
                    </span>
                </span>
            </button>
        @endforeach
    </section>

    {{-- Selected group details + filters --}}
    <section class="card card-pad mt-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-md" style="background-color: {{ $this->activeGroup['color'] }}">
                <x-icon :name="$this->activeGroup['icon']" class="icon h-5 w-5" />
            </span>
            <div class="min-w-0">
                <h4 class="truncate font-display text-base font-semibold text-slate-900 dark:text-white">{{ $this->activeGroup['label'] }}</h4>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $this->activeGroup['description'] }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="badge badge-neutral">{{ count($this->filteredPlugins) }} {{ count($this->filteredPlugins) === 1 ? 'plugin' : 'plugins' }}</span>
            @php $shownActive = collect($this->filteredPlugins)->where('status', 'active')->count(); @endphp
            <span class="badge {{ $shownActive > 0 ? 'badge-success' : 'badge-neutral' }}">
                <x-icon name="dot" class="icon h-2.5 w-2.5" /> {{ $shownActive }} active
            </span>

            <div class="relative">
                <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="search" name="integration_search" wire:model.live.debounce.250ms="search" class="input pl-10" placeholder="Search plugins..." aria-label="Search integrations" />
            </div>

            <select name="integration_status" wire:model.live="status" class="input !w-auto" aria-label="Status filter">
                <option value="all">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </section>

    {{-- Plugin grid --}}
    <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse ($this->filteredPlugins as $key => $plugin)
            <article class="card card-pad card-hover flex flex-col !p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-md" style="background-color: {{ $plugin['color'] }}">
                            <x-icon :name="$plugin['icon']" class="icon h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $plugin['label'] }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $key }}</p>
                        </div>
                    </div>
                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $plugin['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-500/10 text-red-600 dark:text-red-400' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $plugin['status'] === 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        {{ $plugin['status'] === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <p class="mt-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $plugin['description'] }}</p>

                <p class="mt-3 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                    <x-icon :name="$plugin['status'] === 'active' ? 'check-circle' : 'dot'" class="icon h-3.5 w-3.5 {{ $plugin['status'] === 'active' ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600' }}" />
                    {{ $plugin['meta'] }}
                </p>

                <div class="mt-auto pt-4">
                    <a href="{{ route($plugin['manage']) }}" class="btn btn-primary btn-sm w-full justify-center">
                        <x-icon name="settings" class="icon h-3.5 w-3.5" /> Manage
                    </a>
                </div>
            </article>
        @empty
            <p class="col-span-full py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                No integrations match your filters.
            </p>
        @endforelse
    </section>
</div>
