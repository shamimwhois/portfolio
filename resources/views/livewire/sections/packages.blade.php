<div x-data="{ copiedId: null }">
    <div class="glass flex flex-wrap justify-center gap-2 rounded-2xl p-1.5 sm:inline-flex sm:rounded-full" role="tablist" aria-label="Tech stack tabs">
        @foreach ($categories as $key => $label)
            <button
                type="button"
                role="tab"
                aria-selected="{{ $category === $key ? 'true' : 'false' }}"
                wire:click="setCategory('{{ $key }}')"
                class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ $category === $key ? 'bg-white/85 text-slate-900 shadow-md shadow-brand-600/10 ring-1 ring-white/60 dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
            >
                {{ $label }}
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

    @include('livewire.sections.partials.checkout-modal', ['item' => $this->checkoutPackage, 'kind' => 'package'])

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($packages as $pkg)
            <article class="card card-pad card-hover spotlight flex flex-col">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        @if ($pkg->icon)
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500/15 to-brand-700/15 text-sm font-semibold text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:from-brand-400/20 dark:to-brand-600/20 dark:text-brand-300 dark:ring-brand-400/20">{{ $pkg->icon }}</span>
                        @endif
                        <h3 class="font-display text-base font-semibold text-slate-900 dark:text-white">{{ $pkg->title }}</h3>
                    </div>
                    @if ($pkg->version)
                        <span class="badge badge-neutral shrink-0">{{ $pkg->version }}</span>
                    @endif
                </div>

                @if ($pkg->type)
                    <p class="mt-2 text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $pkg->type }}</p>
                @endif

                <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $pkg->description }}</p>

                @if ($pkg->command)
                    <div class="mt-4 flex items-center gap-2 rounded-xl border border-white/50 bg-white/50 px-3 py-2 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                        <code class="flex-1 truncate font-mono text-xs text-slate-600 dark:text-slate-300">{{ $pkg->command }}</code>
                        <button
                            type="button"
                            class="shrink-0 text-slate-400 transition-colors hover:text-slate-900 dark:hover:text-white"
                            @click="navigator.clipboard?.writeText(@js($pkg->command)); copiedId = @js($pkg->id); setTimeout(() => copiedId = null, 1500)"
                            aria-label="Copy command"
                        >
                            <span x-text="copiedId === @js($pkg->id) ? 'Copied' : ''" class="mr-1 text-xs font-medium text-brand-600 dark:text-brand-400"></span>
                            <x-icon name="send" class="icon h-3.5 w-3.5 inline" />
                        </button>
                    </div>
                @endif

                @if ($pkg->isSellable())
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">
                        {{ $pkg->displayPrice() }}
                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400">{{ $pkg->billingLabel() }}</span>
                    </p>
                @endif

                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($pkg->isSellable())
                        <button type="button" wire:click="purchase({{ $pkg->id }})" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                            Buy license
                        </button>
                    @endif
                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        @click="$dispatch('open-package', @js([
                            'title' => $pkg->title,
                            'version' => $pkg->version,
                            'type' => $pkg->type,
                            'description' => $pkg->description,
                            'features' => $pkg->features ?? [],
                            'install_commands' => $pkg->install_commands ?? [],
                            'docsUrl' => $pkg->docs_url,
                            'repoUrl' => $pkg->repo_url,
                        ]))"
                    >
                        Details
                    </button>
                </div>
            </article>
        @empty
            <p class="text-slate-500 dark:text-slate-400">No packages configured yet.</p>
        @endforelse
    </div>
</div>
