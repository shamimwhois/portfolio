<div>
    {{-- Hero --}}
    <section class="container-shell pt-12 sm:pt-16">
        <div class="glass animate-fade-up relative overflow-hidden rounded-3xl px-6 py-10 text-center sm:px-10 sm:py-12">
            <div class="pointer-events-none absolute -left-16 -top-16 h-56 w-56 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-16 -right-16 h-56 w-56 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-2xl">
                <span class="badge badge-brand mx-auto">
                    <x-icon name="doc" class="icon h-3.5 w-3.5" /> Blog
                </span>
                <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                    Articles &amp; <span class="text-brand-600 dark:text-brand-400">insights</span>
                </h1>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Stay updated with the latest tutorials, news, guides, and tips from our team.
                </p>

                <div class="relative mx-auto mt-7 max-w-lg">
                    <x-icon name="search" class="icon pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        class="input !rounded-full !py-3 pl-11 pr-12 text-center"
                        placeholder="Search articles..."
                        aria-label="Search posts"
                    />
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')" class="icon-btn absolute right-2 top-1/2 -translate-y-1/2" aria-label="Clear search">
                            <x-icon name="close" class="icon h-4 w-4" />
                        </button>
                    @endif
                </div>

                {{-- Category pills --}}
                <div class="mt-6 flex flex-wrap justify-center gap-2" role="tablist" aria-label="Blog categories">
                    @foreach (['all' => 'All'] + array_combine(\App\Models\BlogPost::CATEGORIES, \App\Models\BlogPost::CATEGORIES) as $key => $label)
                        <button
                            type="button"
                            role="tab"
                            wire:click="setCategory('{{ $key }}')"
                            class="rounded-full border px-4 py-1.5 text-xs font-semibold uppercase tracking-wide transition-all duration-200 {{ ($category ?: 'all') === $key ? 'border-brand-400/70 bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'border-white/50 bg-white/40 text-slate-600 backdrop-blur-md hover:border-brand-400/50 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-brand-400/40 dark:hover:text-brand-300' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                @if ($search || $category || $tag)
                    <button type="button" wire:click="clearFilters" class="mt-4 text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">
                        Clear filters ({{ collect([$search ? 'search' : null, $category ? 'category' : null, $tag ? '#'.$tag : null])->filter()->implode(', ') }})
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- Content --}}
    <section class="container-shell py-10 sm:py-14">
        @if ($posts->isEmpty())
            <div class="card card-pad text-center">
                <x-icon name="doc" class="icon mx-auto h-8 w-8 text-slate-300 dark:text-slate-600" />
                <p class="mt-3 font-semibold text-slate-900 dark:text-white">No articles found</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">No posts match your current filters.</p>
            </div>
        @else
            <div class="grid items-start gap-10 lg:grid-cols-3">
                {{-- Posts --}}
                <div class="min-w-0 lg:col-span-2">
                    @php $lead = $posts->first(); @endphp

                    {{-- Lead card --}}
                    <a href="{{ route('blog.show', $lead) }}" class="card card-hover group block overflow-hidden">
                        @if ($lead->cover_image)
                            <div class="relative aspect-[16/8] w-full overflow-hidden bg-white/40 dark:bg-white/5">
                                <img src="{{ $lead->cover_image }}" alt="{{ $lead->title }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]" loading="lazy" />
                                <span class="badge badge-brand absolute left-4 top-4 uppercase">{{ $lead->categoryLabel() }}</span>
                                <span class="absolute bottom-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-slate-950/60 px-2.5 py-1 text-[11px] font-medium text-white backdrop-blur-md">
                                    <x-icon name="eye" class="icon h-3 w-3" /> {{ $lead->formattedViews() }} views
                                </span>
                            </div>
                        @endif
                        <div class="p-6 sm:p-7">
                            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="icon h-3.5 w-3.5" /> {{ $lead->published_at?->format('M d, Y') }}</span>
                                <span>· {{ $lead->readTime() }} min read</span>
                            </p>
                            <h2 class="mt-3 font-display text-2xl font-bold leading-snug text-slate-900 transition-colors group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">{{ $lead->title }}</h2>
                            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $lead->excerptOrFallback(160) }}</p>
                            @if (! empty($lead->tags))
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($lead->tags as $t)
                                        <span class="badge badge-neutral">#{{ $t }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/60 pt-4 dark:border-white/10">
                                <span class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white">
                                        {{ strtoupper(substr($lead->author?->name ?? 'A', 0, 1)) }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $lead->author?->name ?? 'Admin' }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 transition-all group-hover:gap-2 dark:text-brand-400">
                                    Read more <x-icon name="arrow-right" class="icon h-4 w-4" />
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Remaining cards --}}
                    <div class="mt-6 grid gap-6 md:grid-cols-2">
                        @foreach ($posts->skip(1) as $post)
                            <a href="{{ route('blog.show', $post) }}" class="card card-hover group flex flex-col overflow-hidden">
                                @if ($post->cover_image)
                                    <div class="relative aspect-[16/9] w-full overflow-hidden bg-white/40 dark:bg-white/5">
                                        <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]" loading="lazy" />
                                        <span class="badge badge-brand absolute left-3 top-3 uppercase">{{ $post->categoryLabel() }}</span>
                                    </div>
                                @endif
                                <div class="flex flex-1 flex-col p-5">
                                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                                        <span class="inline-flex items-center gap-1"><x-icon name="clock" class="icon h-3 w-3" /> {{ $post->published_at?->format('M d, Y') }}</span>
                                        <span>· {{ $post->readTime() }} min read</span>
                                        <span class="inline-flex items-center gap-1"><x-icon name="eye" class="icon h-3 w-3" /> {{ $post->formattedViews() }}</span>
                                    </p>
                                    <h3 class="mt-2 font-display text-lg font-semibold leading-snug text-slate-900 transition-colors group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">{{ $post->title }}</h3>
                                    <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $post->excerptOrFallback(120) }}</p>
                                    @if (! empty($post->tags))
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach (array_slice($post->tags, 0, 3) as $t)
                                                <span class="badge badge-neutral">#{{ $t }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="mt-auto flex items-center justify-between gap-3 pt-4">
                                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $post->author?->name ?? 'Admin' }}</span>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 transition-all group-hover:gap-2 dark:text-brand-400">
                                            Read more <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{ $posts->links('components.pagination') }}
                </div>

                {{-- Sidebar --}}
                <aside class="space-y-6 lg:sticky lg:top-24">
                    {{-- Popular posts --}}
                    @if ($this->popularPosts->isNotEmpty())
                        <div class="glass rounded-2xl p-5">
                            <h2 class="flex items-center gap-2 font-display text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                <x-icon name="zap" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" /> Popular posts
                            </h2>
                            <div class="mt-4 space-y-4">
                                @foreach ($this->popularPosts as $popular)
                                    <a href="{{ route('blog.show', $popular) }}" class="group flex items-start gap-3">
                                        @if ($popular->cover_image)
                                            <span class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-white/50 bg-white/40 dark:border-white/10 dark:bg-white/5">
                                                <img src="{{ $popular->cover_image }}" alt="" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" />
                                            </span>
                                        @else
                                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-300">
                                                <x-icon name="doc" class="icon h-5 w-5" />
                                            </span>
                                        @endif
                                        <span class="min-w-0">
                                            <span class="line-clamp-2 text-sm font-semibold leading-snug text-slate-900 transition-colors group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">{{ $popular->title }}</span>
                                            <span class="mt-1 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                                <span>{{ $popular->published_at?->format('M d, Y') }}</span>
                                                <span class="inline-flex items-center gap-1"><x-icon name="eye" class="icon h-3 w-3" /> {{ $popular->formattedViews() }}</span>
                                            </span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Tags --}}
                    @if ($this->allTags->isNotEmpty())
                        <div class="glass rounded-2xl p-5">
                            <h2 class="flex items-center gap-2 font-display text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                <x-icon name="tag" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" /> Tags
                            </h2>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($this->allTags as $t)
                                    <button
                                        type="button"
                                        wire:click="setTag('{{ $t['name'] }}')"
                                        class="rounded-full border px-3 py-1 text-xs font-medium transition-all duration-200 {{ $tag === $t['name'] ? 'border-brand-400/70 bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'border-white/50 bg-white/40 text-slate-600 backdrop-blur-md hover:border-brand-400/50 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-brand-400/40 dark:hover:text-brand-300' }}"
                                    >
                                        #{{ $t['name'] }} <span class="opacity-60">{{ $t['count'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Categories --}}
                    @if (! empty($this->categories))
                        <div class="glass rounded-2xl p-5">
                            <h2 class="flex items-center gap-2 font-display text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                <x-icon name="folder" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" /> Categories
                            </h2>
                            <ul class="mt-3 divide-y divide-slate-200/60 dark:divide-white/10">
                                @foreach ($this->categories as $name => $count)
                                    <li>
                                        <button
                                            type="button"
                                            wire:click="setCategory('{{ $name }}')"
                                            class="group flex w-full items-center justify-between gap-3 py-2.5 text-left text-sm font-medium text-slate-600 transition-colors hover:text-brand-700 dark:text-slate-300 dark:hover:text-brand-300"
                                        >
                                            <span class="flex items-center gap-2">
                                                <x-icon name="chevron-right" class="icon h-3.5 w-3.5 text-slate-400 transition-transform group-hover:translate-x-0.5 dark:text-slate-500" />
                                                {{ $name }}
                                            </span>
                                            <span class="badge {{ $category === $name ? 'badge-brand' : 'badge-neutral' }}">{{ $count }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </aside>
            </div>
        @endif
    </section>
</div>
