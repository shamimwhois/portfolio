<div>
    {{-- Hero --}}
    <section class="container-shell pt-14 sm:pt-20">
        <div class="glass animate-fade-up relative overflow-hidden rounded-3xl p-8 sm:p-12">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>
            <div class="relative grid items-center gap-10 lg:grid-cols-[1fr_auto]">
                <div>
                    <span class="badge badge-brand">
                        <x-icon name="zap" class="icon h-3.5 w-3.5" /> Group Buy Marketplace
                    </span>
                    <h1 class="mt-4 max-w-2xl font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        {{ \App\Models\Setting::get('site_name', 'MD SHAMIM HOSSAIN') }} — Best Group Buy
                        <span class="text-brand-600 dark:text-brand-400">SEO Tools Service</span>
                    </h1>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Premium tool subscriptions shared at a fraction of the retail price — instant access,
                        lowest price guarantee, and support that actually answers.
                    </p>
                    <ul class="mt-6 flex flex-wrap gap-x-8 gap-y-3">
                        @foreach (['Lowest Price Guarantee', 'Instant Access', '24/7 Support'] as $point)
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
                        <x-icon name="box" class="icon relative h-20 w-20 text-brand-500" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Kind tabs + search --}}
    <section class="container-shell mt-10">
        <div class="glass flex flex-wrap items-center justify-between gap-3 rounded-2xl p-3 sm:p-4">
            <div class="flex flex-wrap justify-center gap-1.5" role="tablist" aria-label="Group buy types">
                <button
                    type="button"
                    role="tab"
                    wire:click="setKind('all')"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $kind === 'all' ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                >
                    All
                    <span class="ml-1.5 rounded-full bg-slate-900/10 px-1.5 py-0.5 text-[10px] font-bold dark:bg-white/10">{{ $this->comboTotal + $this->toolTotal }}</span>
                </button>
                <button
                    type="button"
                    role="tab"
                    wire:click="setKind('{{ \App\Models\GroupPackage::KIND_COMBO }}')"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $kind === \App\Models\GroupPackage::KIND_COMBO ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                >
                    Combo
                    <span class="ml-1.5 rounded-full bg-slate-900/10 px-1.5 py-0.5 text-[10px] font-bold dark:bg-white/10">{{ $this->comboTotal }}</span>
                </button>
                <button
                    type="button"
                    role="tab"
                    wire:click="setKind('{{ \App\Models\GroupPackage::KIND_TOOL }}')"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $kind === \App\Models\GroupPackage::KIND_TOOL ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                >
                    Single Tools
                    <span class="ml-1.5 rounded-full bg-slate-900/10 px-1.5 py-0.5 text-[10px] font-bold dark:bg-white/10">{{ $this->toolTotal }}</span>
                </button>
            </div>
            <div class="relative w-full sm:max-w-xs">
                <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="search" name="group_search" wire:model.live.debounce.300ms="search" class="input pl-10" placeholder="Search group buy tools..." aria-label="Search group buy tools" />
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

    {{-- Combo packages --}}
    @if ($kind === 'all' || $kind === \App\Models\GroupPackage::KIND_COMBO)
        <section class="container-shell mt-10">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="box" class="icon h-4 w-4" />
                </span>
                <h2 class="font-display text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white">✨ COMBO PACKAGE ✨</h2>
                <span class="badge badge-neutral">{{ $this->comboTotal }} {{ $this->comboTotal === 1 ? 'package' : 'packages' }}</span>
            </div>
            <p class="mt-2 max-w-xl text-sm text-slate-500 dark:text-slate-400">Curated bundles of premium tools for a single monthly price — one order, every license you need.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($this->combos->take($this->comboLimit) as $combo)
                    @php $buyable = $combo->isPublished() && $combo->isSellable(); @endphp
                    <article class="card card-pad card-hover group relative flex flex-col transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-600/10">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-base font-bold text-white shadow-md shadow-brand-600/25">{{ $combo->icon() }}</span>
                            <span class="{{ $combo->isPublished() ? 'badge badge-success' : 'badge badge-neutral' }} uppercase">
                                {{ $combo->isPublished() ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <h3 class="mt-4 font-display text-base font-semibold text-slate-900 dark:text-white">{{ $combo->title }}</h3>
                        <p class="mt-2">
                            <span class="font-display text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $combo->displayPrice() }}</span>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">/ month</span>
                        </p>
                        <ul class="mt-4 flex-1 space-y-1.5">
                            @foreach ($combo->features ?? [] as $feature)
                                <li class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                    <x-icon name="check" class="icon h-4 w-4 shrink-0 text-emerald-500" />
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-auto pt-5">
                            @if ($buyable)
                                <button type="button" wire:click="purchase({{ $combo->id }})" wire:loading.attr="disabled" class="btn btn-primary w-full">
                                    Order Now
                                    <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
                                </button>
                            @else
                                <button type="button" disabled class="btn w-full cursor-not-allowed opacity-50" style="background: var(--track-bg); color: rgb(100 116 139 / 1);">Stock Out</button>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="col-span-full py-10 text-center text-sm text-slate-500 dark:text-slate-400">No combo packages available yet.</p>
                @endforelse
            </div>

            @if ($this->hasMoreCombos)
                <div class="mt-8 flex flex-col items-center gap-2">
                    <button type="button" wire:click="loadMoreCombos" wire:loading.attr="disabled" class="btn btn-secondary">
                        <x-icon name="plus" class="icon h-4 w-4" /> Load more packages
                    </button>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->comboLimit, $this->comboTotal) }} of {{ $this->comboTotal }}</p>
                </div>
            @endif
        </section>
    @endif

    {{-- Single tools --}}
    @if ($kind === 'all' || $kind === \App\Models\GroupPackage::KIND_TOOL)
        <section class="container-shell mt-14">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="wrench" class="icon h-4 w-4" />
                </span>
                <h2 class="font-display text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white">🔥 GROUP BUY SINGLE TOOLS 🔥</h2>
                <span class="badge badge-neutral">{{ $this->toolTotal }} {{ $this->toolTotal === 1 ? 'tool' : 'tools' }}</span>
            </div>
            <p class="mt-2 max-w-xl text-sm text-slate-500 dark:text-slate-400">Need just one tool? Pick it, order it, and your shared license is delivered after payment confirmation.</p>

            <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @forelse ($this->tools->take($this->toolLimit) as $tool)
                    @php $buyable = $tool->isPublished() && $tool->isSellable(); @endphp
                    <article class="card card-pad card-hover group relative flex flex-col transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-600/10">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-sm font-bold text-white shadow-md shadow-brand-600/25">
                                {{ $tool->icon() }}
                            </span>
                            <span class="{{ $tool->isPublished() ? 'badge badge-success' : 'badge badge-neutral' }} uppercase">
                                {{ $tool->isPublished() ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <h3 class="mt-3 font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $tool->title }}</h3>
                        <p class="mt-1 font-display text-lg font-bold text-brand-600 dark:text-brand-400">{{ $tool->displayPrice() }}</p>
                        <div class="mt-auto pt-3">
                            @if ($buyable)
                                <button type="button" wire:click="purchase({{ $tool->id }})" wire:loading.attr="disabled" class="btn btn-primary btn-sm w-full">
                                    Order Now
                                    <x-icon name="arrow-right" class="icon h-3 w-3" />
                                </button>
                            @else
                                <button type="button" disabled class="btn btn-sm w-full cursor-not-allowed opacity-50" style="background: var(--track-bg); color: rgb(100 116 139 / 1);">Stock Out</button>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="col-span-full py-10 text-center text-sm text-slate-500 dark:text-slate-400">No single tools available yet.</p>
                @endforelse
            </div>

            @if ($this->hasMoreTools)
                <div class="mt-8 flex flex-col items-center gap-2">
                    <button type="button" wire:click="loadMoreTools" wire:loading.attr="disabled" class="btn btn-secondary">
                        <x-icon name="plus" class="icon h-4 w-4" /> Load more tools
                    </button>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->toolLimit, $this->toolTotal) }} of {{ $this->toolTotal }}</p>
                </div>
            @endif
        </section>
    @endif

    {{-- Why choose us --}}
    <section class="container-shell mt-16">
        <div class="text-center">
            <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">Why You Must Choose Us?</h2>
        </div>
        <div class="mt-8 grid gap-5 sm:grid-cols-3">
            @foreach ([
                ['Fast Delivery', 'Licenses are activated within hours of payment confirmation, not days.'],
                ['Highest Uptime', 'Shared accounts are monitored around the clock so your tools stay live.'],
                ['Dedicated Support', 'A real human on WhatsApp, Telegram and email — 24/7, no ticket queues.'],
            ] as [$title, $copy])
                <div class="glass rounded-2xl p-6 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                        <x-icon name="check-circle" class="icon h-6 w-6" />
                    </span>
                    <h3 class="mt-4 font-display text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $copy }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Footer note --}}
    <section class="container-shell pb-20">
        <p class="mt-14 text-center text-xs text-slate-500 dark:text-slate-400">
            Prefer developer toolkits? Browse the <a href="{{ route('servicelist') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Service List</a> ·
            licenses are delivered after payment confirmation via our support channels.
        </p>
    </section>
</div>
