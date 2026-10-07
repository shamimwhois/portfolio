<div>
    <div class="glass flex flex-wrap justify-center gap-2 rounded-2xl p-1.5 sm:inline-flex sm:rounded-full" role="tablist" aria-label="Service categories">
        @foreach ($this->categories as $cat)
            <button
                type="button"
                role="tab"
                id="service-tab-{{ $cat->slug }}"
                aria-selected="{{ $categoryId === $cat->id ? 'true' : 'false' }}"
                wire:click="setCategory({{ $cat->id }})"
                class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $categoryId === $cat->id ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
            >
                {{ $cat->name }}
            </button>
        @endforeach
    </div>

    @if ($message)
        <div class="mx-auto mt-8 max-w-2xl">
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                <x-icon name="check-circle" class="icon h-5 w-5 shrink-0" />
                <p>{{ $message }}</p>
            </div>
        </div>
    @endif

    @include('livewire.sections.partials.checkout-modal', ['item' => $this->checkoutTier, 'kind' => 'plan'])

    <div class="mt-10 grid gap-6 md:grid-cols-3" role="tabpanel" aria-label="Pricing tiers">
        @forelse ($this->tiers as $tier)
            <article class="card card-pad spotlight relative flex flex-col {{ $tier->popular ? 'border-brand-300/80 shadow-lg shadow-brand-600/15 ring-1 ring-brand-300/60 dark:border-brand-500/50 dark:ring-brand-500/30' : 'card-hover' }}">
                @if ($tier->popular)
                    <span class="badge badge-brand absolute -top-3 left-1/2 -translate-x-1/2 shadow-sm">Most popular</span>
                @endif

                <h3 class="font-display text-base font-semibold text-slate-900 dark:text-white">{{ $tier->name }}</h3>
                <p class="mt-3">
                    <span class="font-display text-3xl font-bold text-slate-900 dark:text-white">{{ $tier->price }}</span>
                    @if ($tier->billingLabel())
                        <span class="ml-1 text-sm text-slate-500 dark:text-slate-400">{{ $tier->billingLabel() }}</span>
                    @endif
                </p>

                <ul class="mt-6 flex-1 space-y-3">
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
                    class="mt-8 {{ $tier->cta === 'gradient' ? 'btn btn-primary' : 'btn btn-secondary' }} w-full"
                >
                    Choose plan
                </button>
            </article>
        @empty
            <p class="text-slate-500 dark:text-slate-400">No plans configured for this category.</p>
        @endforelse
    </div>
</div>
