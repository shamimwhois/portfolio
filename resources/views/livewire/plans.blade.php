<div>
    {{-- Hero --}}
    <section class="container-shell pt-14 sm:pt-20">
        <div class="glass animate-fade-up relative overflow-hidden rounded-3xl p-8 sm:p-12">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>
            <div class="relative text-center">
                <span class="badge badge-brand mx-auto">
                    <x-icon name="layers" class="icon h-3.5 w-3.5" /> Subscription Plans
                </span>
                <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    Plans &amp; <span class="text-brand-600 dark:text-brand-400">Pricing</span>
                </h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Clear scopes, transparent pricing, and delivery you can rely on. Pick a category, compare the
                    plans, and order instantly — payment is confirmed through your preferred gateway or a manual method.
                </p>
                <ul class="mt-6 flex flex-wrap justify-center gap-x-8 gap-y-3">
                    @foreach (['Instant ordering', 'Secure payment', 'Live project tracker'] as $point)
                        <li class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                <x-icon name="check" class="icon h-3 w-3" />
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- Category tabs + search --}}
    <section class="container-shell mt-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="glass flex flex-wrap justify-center gap-1.5 rounded-2xl p-1.5 sm:rounded-full" role="tablist" aria-label="Plan categories">
                <button
                    type="button"
                    role="tab"
                    wire:click="setCategory('all')"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $category === 'all' ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                >
                    All plans
                </button>
                @foreach ($this->categories as $cat)
                    <button
                        type="button"
                        role="tab"
                        wire:click="setCategory('{{ $cat->id }}')"
                        class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $category === (string) $cat->id ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                    >
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
            <div class="relative w-full sm:max-w-sm">
                <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="search" name="plan_search" wire:model.live.debounce.300ms="search" class="input pl-10" placeholder="Search plans..." aria-label="Search plans" />
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

        @include('livewire.sections.partials.checkout-modal', ['item' => $this->checkoutTier, 'kind' => 'plan'])
    </section>

    {{-- Grouped plan sections --}}
    <section class="container-shell mt-10 pb-20">
        @forelse ($this->groups as $group)
            <div class="mb-12 last:mb-0">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                        <x-icon name="layers" class="icon h-4 w-4" />
                    </span>
                    <h2 class="font-display text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $group['name'] }}</h2>
                    <span class="badge badge-neutral">{{ $group['count'] }} {{ $group['count'] === 1 ? 'plan' : 'plans' }}</span>
                </div>

                <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($group['tiers'] as $tier)
                        <article class="card card-pad spotlight relative flex flex-col {{ $tier->popular ? 'border-brand-300/80 shadow-lg shadow-brand-600/15 ring-1 ring-brand-300/60 dark:border-brand-500/50 dark:ring-brand-500/30' : 'card-hover' }}">
                            @if ($tier->popular)
                                <span class="badge badge-brand absolute -top-3 left-1/2 -translate-x-1/2 shadow-sm">Most popular</span>
                            @endif
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-display text-base font-semibold text-slate-900 dark:text-white">{{ $tier->name }}</h3>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $group['name'] }}</p>
                                </div>
                                <span class="badge badge-neutral shrink-0">{{ $tier->billingLabel() }}</span>
                            </div>
                            <p class="mt-4">
                                <span class="font-display text-3xl font-bold text-slate-900 dark:text-white">{{ $tier->price }}</span>
                                @if ($tier->billingLabel())
                                    <span class="ml-1 text-sm text-slate-500 dark:text-slate-400">/ {{ $tier->billing === 'month' ? 'month' : 'project' }}</span>
                                @endif
                            </p>
                            <ul class="mt-5 flex-1 space-y-2.5">
                                @foreach (($tier->features ?? []) as $feature)
                                    <li class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-400">
                                        <x-icon name="check" class="icon mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <button
                                type="button"
                                wire:click="purchase({{ $tier->id }})"
                                class="mt-7 {{ $tier->cta === 'gradient' ? 'btn btn-primary' : 'btn btn-secondary' }} w-full"
                            >
                                Choose plan
                                <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
                            </button>
                        </article>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card card-pad py-14 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-500/10 text-slate-400">
                    <x-icon name="layers" class="icon h-6 w-6" />
                </span>
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No plans match your search.</p>
                <button type="button" wire:click="$set('search', '')" class="btn btn-secondary btn-sm mt-4">Clear search</button>
            </div>
        @endforelse
    </section>
</div>
