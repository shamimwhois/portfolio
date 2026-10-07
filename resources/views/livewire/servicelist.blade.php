<div>
    {{-- Hero --}}
    <section class="container-shell pt-14 sm:pt-20">
        <div class="glass animate-fade-up relative overflow-hidden rounded-3xl p-8 sm:p-12">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>
            <div class="relative grid items-center gap-10 lg:grid-cols-[1fr_auto]">
                <div>
                    <span class="badge badge-brand">
                        <x-icon name="zap" class="icon h-3.5 w-3.5" /> Extension Base Tools Service
                    </span>
                    <h1 class="mt-4 max-w-2xl font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        {{ \App\Models\Setting::get('site_name', 'MD SHAMIM HOSSAIN') }} Tool Service
                    </h1>
                    <p class="mt-2 font-semibold text-slate-700 dark:text-slate-200">Best Tools Provider — direct licenses, no high monthly fees</p>
                    <p class="mt-4 max-w-xl text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Pick a tool, choose your validity, and buy instantly. Payment is settled through your preferred
                        gateway or our support channels — after confirmation your license is delivered to your panel.
                    </p>
                    <ul class="mt-6 flex flex-wrap gap-x-8 gap-y-3">
                        @foreach (['Instant delivery', 'Secure payment', 'Direct licenses'] as $point)
                            <li class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                    <x-icon name="check" class="icon h-3 w-3" />
                                </span>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="hidden lg:block" aria-hidden="true">
                    <div class="relative flex h-44 w-44 items-center justify-center">
                        <span class="absolute inset-0 rounded-full border border-dashed border-brand-400/40 animate-[spin_24s_linear_infinite]"></span>
                        <span class="absolute inset-5 rounded-full bg-brand-500/10 ring-1 ring-inset ring-brand-400/30"></span>
                        <x-icon name="wrench" class="icon relative h-20 w-20 text-brand-500" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Category tabs + search --}}
    <section class="container-shell mt-10">
        <div class="glass flex flex-wrap items-center justify-between gap-3 rounded-2xl p-3 sm:p-4">
            <div class="flex flex-wrap justify-center gap-1.5" role="tablist" aria-label="Tool categories">
                <button
                    type="button"
                    role="tab"
                    wire:click="setCategory('all')"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $category === 'all' ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                >
                    All Tools
                    <span class="ml-1.5 rounded-full bg-slate-900/10 px-1.5 py-0.5 text-[10px] font-bold dark:bg-white/10">{{ $this->tabCounts->sum() }}</span>
                </button>
                @foreach ($this->categories as $key => $label)
                    <button
                        type="button"
                        role="tab"
                        wire:click="setCategory('{{ $key }}')"
                        class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $category === $key ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                    >
                        {{ $label }}
                        <span class="ml-1.5 rounded-full bg-slate-900/10 px-1.5 py-0.5 text-[10px] font-bold dark:bg-white/10">{{ $this->tabCounts[$key] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>
            <div class="relative w-full sm:max-w-xs">
                <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="search" name="tool_search" wire:model.live.debounce.300ms="search" class="input pl-10" placeholder="Search your tool..." aria-label="Search tools" />
            </div>
        </div>

        @if ($message)
            <div class="mt-6">
                <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <x-icon name="check-circle" class="icon h-5 w-5 shrink-0" />
                    <p>{{ $message }}</p>
                </div>
            </div>
        @endif

        @include('livewire.sections.partials.checkout-modal', ['item' => $this->checkoutPackage, 'kind' => 'package'])
    </section>

    {{-- Grouped catalog --}}
    <section class="container-shell mt-10 pb-20">
        @forelse ($this->groups as $group)
            <div class="mb-12 last:mb-0">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                        <x-icon name="wrench" class="icon h-4 w-4" />
                    </span>
                    <h2 class="font-display text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $group['label'] }}</h2>
                    @php $groupTotal = $this->tabCounts[$group['key']] ?? $group['count']; @endphp
                    <span class="badge badge-neutral">{{ $groupTotal }} {{ $groupTotal === 1 ? 'tool' : 'tools' }}</span>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($group['items'] as $pkg)
                        @php $buyable = $pkg->isPublished() && $pkg->isSellable(); @endphp
                        <article class="card card-pad card-hover group relative flex flex-col transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-600/10">
                            <div class="flex items-start justify-between gap-3">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-base font-bold text-white shadow-md shadow-brand-600/25">
                                    {{ $pkg->icon ?: substr($pkg->title, 0, 2) }}
                                </span>
                                @if ($pkg->isPublished())
                                    <span class="badge badge-success uppercase">Active</span>
                                @else
                                    <span class="badge badge-neutral uppercase">Inactive</span>
                                @endif
                            </div>

                            <h3 class="mt-4 pr-2 font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $pkg->title }}</h3>
                            @if ($pkg->version)
                                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">v{{ $pkg->version }}{{ $pkg->type ? ' · '.$pkg->type : '' }}</p>
                            @endif
                            @if ($pkg->description)
                                <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $pkg->description }}</p>
                            @endif

                            <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-200/60 pt-3 text-xs dark:border-white/10">
                                <span class="font-mono text-slate-400">#{{ $pkg->id }}</span>
                                <span class="badge badge-neutral">{{ $pkg->categoryLabel() }}</span>
                            </div>

                            <div class="mt-3">
                                <select
                                    name="validity_{{ $pkg->id }}"
                                    wire:change="chooseValidity({{ $pkg->id }}, $event.target.value)"
                                    class="input w-full !py-1.5 !pr-8 text-xs"
                                    aria-label="Select validity for {{ $pkg->title }}"
                                    @disabled(! $buyable)
                                >
                                    @foreach (\App\Models\TechPackage::VALIDITIES as $validity)
                                        <option value="{{ $validity }}" @selected(($this->validities[$pkg->id] ?? $pkg->validity) === $validity)>{{ $validity }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                                @if ($pkg->isSellable())
                                    <span class="font-display text-lg font-bold text-brand-600 dark:text-brand-400">{{ $pkg->displayPrice() }}/-</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                                @if ($buyable)
                                    <button type="button" wire:click="purchase({{ $pkg->id }})" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                        Buy Now
                                        <x-icon name="arrow-right" class="icon h-3 w-3" />
                                    </button>
                                @else
                                    <button type="button" disabled class="btn btn-sm cursor-not-allowed opacity-50" style="background: var(--track-bg); color: rgb(100 116 139 / 1);">Stock Out</button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card card-pad py-14 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-500/10 text-slate-400">
                    <x-icon name="search" class="icon h-6 w-6" />
                </span>
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No tools found. Try a different search or category.</p>
                <button type="button" wire:click="$set('search', '')" class="btn btn-secondary btn-sm mt-4">Clear search</button>
            </div>
        @endforelse

        @if ($this->hasMore)
            <div class="mt-8 flex flex-col items-center gap-2">
                <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary">
                    <x-icon name="plus" class="icon h-4 w-4" /> Load more tools
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }} tools</p>
            </div>
        @endif

        <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
            Need a Combo Package? <a href="{{ route('servicegroup') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Check our group buy combos</a> · License delivered after payment confirmation via our support channels.
        </p>
    </section>
</div>
