<div>
    {{-- Header --}}
    <section class="card card-pad flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Social Channels</p>
            <h2 class="card-title mt-1 text-2xl">Social Integration</h2>
            <p class="card-subtitle">
                Connect your profiles so visitors can reach you everywhere, add one-click share buttons to your
                blog and feed, and embed a live social feed on the homepage.
            </p>
        </div>
        <span class="badge {{ $this->metrics['embed'] ? 'badge-success' : 'badge-neutral' }}">
            <x-icon name="{{ $this->metrics['embed'] ? 'check-circle' : 'x' }}" class="icon h-3.5 w-3.5" />
            {{ $this->metrics['embed'] ? 'Embedded feed active' : 'No embedded feed' }}
        </span>
    </section>

    {{-- Metric cards --}}
    <section class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-3">
        <div class="card card-pad">
            <p class="stat-label">Connected</p>
            <p class="stat-value mt-1 text-emerald-600 dark:text-emerald-400">{{ $this->metrics['connected'] }}<span class="text-sm font-medium text-slate-400">/{{ $this->metrics['total'] }}</span></p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enabled with a URL</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Share buttons</p>
            <p class="stat-value mt-1 text-brand-600 dark:text-brand-400">5</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Networks on blog &amp; feed</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Embedded feed</p>
            <p class="stat-value mt-1 text-slate-900 dark:text-white">{{ $this->metrics['embed'] ? 'On' : 'Off' }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Homepage social section</p>
        </div>
    </section>

    <form wire:submit="save" class="mt-6 space-y-6">
        {{-- Channel directory --}}
        <section class="card card-pad">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="card-title text-base">Channel directory</h3>
                    <p class="card-subtitle">Enable a channel and paste its profile URL — connected channels render in the site footer.</p>
                </div>
                <div class="relative w-full sm:max-w-xs">
                    <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="social_search" wire:model.live.debounce.250ms="search" class="input pl-10" placeholder="Search channels..." aria-label="Search channels" />
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($this->filteredChannels as $key => $channel)
                    <article class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-md" style="background-color: {{ $channel['color'] }}">
                                    <x-icon :name="$channel['icon']" class="icon h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $channel['label'] }}</p>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $key }}</p>
                                </div>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input
                                    type="checkbox"
                                    class="peer sr-only"
                                    wire:model="channels.{{ $key }}.enabled"
                                />
                                <span class="relative h-6 w-11 rounded-full bg-slate-300 transition-colors duration-200 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 peer-checked:bg-emerald-500 peer-checked:after:translate-x-5 dark:bg-white/15"></span>
                            </label>
                        </div>

                        <div class="mt-4">
                            <label class="label !mb-1.5 text-xs">Profile URL</label>
                            <input
                                type="url"
                                name="social_url_{{ $key }}"
                                wire:model="channels.{{ $key }}.url"
                                class="input !py-2 text-sm"
                                placeholder="{{ $channel['placeholder'] }}"
                                aria-label="{{ $channel['label'] }} URL"
                            />
                            @error('channels.'.$key.'.url') <p class="error-text mt-1">{{ $message }}</p> @enderror
                        </div>

                        <p class="mt-3 text-xs {{ ! empty($this->channels[$key]['enabled']) && filled($this->channels[$key]['url'] ?? '') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }}">
                            <x-icon name="{{ ! empty($this->channels[$key]['enabled']) && filled($this->channels[$key]['url'] ?? '') ? 'check-circle' : 'dot' }}" class="icon h-3.5 w-3.5" />
                            {{ ! empty($this->channels[$key]['enabled']) && filled($this->channels[$key]['url'] ?? '') ? 'Connected' : 'Not connected' }}
                        </p>
                    </article>
                @empty
                    <p class="col-span-full py-10 text-center text-sm text-slate-500 dark:text-slate-400">No channels match your search.</p>
                @endforelse
            </div>
        </section>

        {{-- Share buttons info --}}
        <section class="card card-pad">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="share" class="icon h-4 w-4" />
                </span>
                <div>
                    <h3 class="card-title text-base">Share buttons</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">One-click sharing on Facebook, X, LinkedIn, WhatsApp and Telegram.</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                @foreach (\App\Support\Social::shareLinks(route('home'), 'Check this out') as $share)
                    <a
                        href="{{ $share['url'] }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-white shadow-md"
                        style="background-color: {{ $share['color'] }}"
                    >
                        <x-icon :name="$share['icon']" class="icon h-4 w-4" /> {{ $share['label'] }}
                    </a>
                @endforeach
                <p class="ml-auto text-xs text-slate-500 dark:text-slate-400">
                    Auto-applied to every blog post and feed post.
                </p>
            </div>
        </section>

        {{-- Embedded feed --}}
        <section class="card card-pad">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                        <x-icon name="globe" class="icon h-4 w-4" />
                    </span>
                    <div>
                        <h3 class="card-title text-base">Embedded feed</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Paste a third-party embed snippet (Instagram post, X timeline, etc.) to show a live social feed on the homepage.</p>
                    </div>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" class="peer sr-only" wire:model="embed_enabled" />
                    <span class="relative h-6 w-11 rounded-full bg-slate-300 transition-colors duration-200 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 peer-checked:bg-brand-600 peer-checked:after:translate-x-5 dark:bg-white/15"></span>
                </label>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <x-admin.field label="Section title" name="embed_title" wire:model="embed_title" placeholder="From the community" />
                </div>
                <div class="md:col-span-2">
                    <label class="label">Embed snippet</label>
                    <textarea
                        name="embed_html"
                        wire:model="embed_html"
                        rows="5"
                        class="input resize-none font-mono text-xs"
                        placeholder="<blockquote class=&quot;instagram-media&quot; ...></blockquote>"
                    ></textarea>
                    @error('embed_html') <p class="error-text mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Save bar --}}
        <div class="sticky bottom-4 z-10 flex justify-end">
            <div class="glass-strong flex items-center gap-3 rounded-2xl px-4 py-3 shadow-xl">
                <p class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">Connected channels appear in the footer immediately.</p>
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                    <x-icon name="check" class="icon h-3.5 w-3.5" /> Save Settings
                </button>
            </div>
        </div>
    </form>
</div>
