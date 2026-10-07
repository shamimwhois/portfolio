@php use App\Models\Setting; @endphp
<div>
    @if (Setting::get('sections.hero', true))
    <section id="hero" class="container-shell py-14 sm:py-20 lg:py-28">
        <div class="grid items-center gap-12 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16">
            <div class="relative">
                <div class="pointer-events-none absolute -left-24 -top-24 h-64 w-64 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <p class="animate-fade-up inline-flex items-center gap-2 rounded-full border border-emerald-300/60 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 backdrop-blur-md dark:border-emerald-400/30 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <span class="animate-pulse-dot h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        {{ $settings['hero_kicker'] ?? 'Available for new projects' }}
                    </p>
                    <h1 class="animate-fade-up delay-75 mt-5 font-display text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                        <span class="bg-gradient-to-r from-brand-600 via-violet-600 to-brand-500 bg-clip-text text-transparent dark:from-brand-300 dark:via-violet-300 dark:to-brand-400">
                            {{ $settings['hero_title'] ?? 'MD SHAMIM HOSSAIN' }}
                        </span>
                    </h1>
                    <p class="animate-fade-up delay-150 mt-4 font-display text-xl font-semibold text-slate-700 sm:text-2xl dark:text-slate-200">
                        {{ $typewriterLines[0] ?? 'Senior Laravel &amp; PHP Developer' }}
                    </p>
                    <p class="animate-fade-up delay-200 mt-5 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $settings['hero_text'] ?? 'I design and build reliable, scalable web platforms with Laravel, Livewire and modern front-end tooling — turning business goals into production-grade software.' }}
                    </p>
                    <div class="animate-fade-up delay-200 mt-8 flex flex-wrap items-center gap-4">
                        <a href="#services" class="btn btn-primary btn-lg">
                            Hire me
                            <x-icon name="arrow-right" class="icon h-4 w-4" />
                        </a>
                        <a href="#projects" class="btn btn-secondary btn-lg">View Projects</a>
                    </div>

                    @if (count($skills) > 0)
                        <div class="animate-fade-up delay-200 mt-8 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Core stack</span>
                            @foreach ($skills->take(4) as $skill)
                                <span class="badge badge-neutral">{{ $skill->icon ? $skill->icon.' ' : '' }}{{ $skill->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="animate-fade-up delay-150 relative mx-auto w-full max-w-sm lg:max-w-md">
                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-violet-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative rounded-[1.35rem] bg-gradient-to-br from-brand-300/60 via-white/40 to-violet-300/50 p-[1px] shadow-xl shadow-brand-600/15 dark:from-brand-500/40 dark:via-white/10 dark:to-violet-500/30">
                    <div class="relative overflow-hidden rounded-[1.3rem] border border-white/60 bg-white/40 backdrop-blur-xl dark:border-white/10">
                        <img
                            src="{{ asset('assets/image/pic.jpg') }}"
                            alt="Portrait of {{ $settings['site_name'] ?? 'MD SHAMIM HOSSAIN' }}"
                            class="aspect-[4/5] w-full object-cover"
                            loading="lazy"
                        />
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-brand-950/20 to-transparent" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="card card-pad spotlight absolute -bottom-5 left-1/2 flex w-[86%] -translate-x-1/2 items-center gap-3 shadow-lg shadow-brand-600/15">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-700 text-white shadow-md shadow-brand-600/30">
                        <x-icon name="check-circle" class="icon h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Open to work</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">Laravel · PHP · Livewire</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (count($skills) > 0)
    <section class="border-y border-slate-200/40 bg-white/20 py-5 backdrop-blur-xl dark:border-white/5 dark:bg-white/[0.02]" aria-label="Technologies I work with">
        <div class="marquee-mask overflow-hidden">
            <div class="animate-marquee flex w-max items-center gap-10 pr-10">
                @foreach ($skills->concat($skills) as $skill)
                    <span class="flex shrink-0 items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                        @if ($skill->icon) <span class="text-base" aria-hidden="true">{{ $skill->icon }}</span> @endif
                        {{ $skill->name }}
                        <span class="text-brand-400" aria-hidden="true">✦</span>
                    </span>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endif

    @if (Setting::get('sections.about', true))
    <div >
    <section id="about" class="container-shell scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="grid items-start gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">About</p>
                <h2 class="section-heading mt-3">Engineering dependable software for real businesses</h2>
                <div class="mt-6 space-y-4 text-base leading-relaxed text-slate-600 dark:text-slate-400">
                    <p>
                        <strong class="font-semibold text-slate-900 dark:text-white">{{ $settings['site_name'] ?? 'MD SHAMIM HOSSAIN' }}</strong> —
                        {{ $settings['about_text'] ?? 'Senior Laravel and PHP specialist focused on resilient backend architecture, scalable API design, and polished user-first digital products.' }}
                    </p>
                    <p>
                        From clean database schemas and secure auth flows to high-performance APIs and maintainable codebases, I help startups and growing teams ship software that works — and keeps working.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                @forelse ($stats as $stat)
                    <div class="card card-pad">
                        <p class="stat-value">{{ $stat['value'] ?? '' }}</p>
                        <p class="stat-label mt-1">{{ $stat['label'] ?? '' }}</p>
                    </div>
                @empty
                    @foreach ([['8+', 'Years of experience'], ['150+', 'Projects delivered'], ['24/7', 'Support mindset'], ['100%', 'Commitment']] as [$value, $label])
                        <div class="card card-pad">
                            <p class="stat-value">{{ $value }}</p>
                            <p class="stat-label mt-1">{{ $label }}</p>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>
    </div>
    @endif

    @if (Setting::get('sections.stack', true))
    <section id="stack" class="container-shell scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Tech Stack</p>
            <h2 class="section-heading mt-3">Packages &amp; components I ship</h2>
            <p class="section-lead mx-auto">Production-ready building blocks, from UI kits to full-stack boilerplates.</p>
        </div>
        <div class="mt-10">
            <livewire:sections.packages />
        </div>
    </section>
    @endif

    @if (Setting::get('sections.skills', true))
    <div >
    <section id="skills" class="container-shell scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Skills</p>
            <h2 class="section-heading mt-3">What I bring to the table</h2>
        </div>
        <div class="mt-12 grid gap-x-12 gap-y-8 md:grid-cols-2">
            @forelse ($skills as $skill)
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $skill->name }}</p>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            @if ($skill->icon) {{ $skill->icon }} · @endif{{ $skill->level }}%
                        </span>
                    </div>
                    <div class="progress-track mt-2.5">
                        <div class="progress-fill" style="width: {{ min(100, max(0, $skill->level)) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-slate-500 dark:text-slate-400">No skills configured yet.</p>
            @endforelse
        </div>
    </section>
    </div>
    @endif

    @if (Setting::get('sections.projects', true))
    <section id="projects" class="container-shell scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Portfolio</p>
            <h2 class="section-heading mt-3">Selected work</h2>
            <p class="section-lead mx-auto">A few products and platforms I have designed and built end to end.</p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($projects->take($projectLimit) as $project)
                <article class="card card-hover flex flex-col overflow-hidden">
                    @if ($project->thumbnail)
                        <div class="aspect-[16/9] w-full overflow-hidden bg-white/40 dark:bg-white/5">
                            <img class="h-full w-full object-cover transition-transform duration-300 hover:scale-[1.03]" src="{{ $project->thumbnail }}" alt="{{ $project->title }}" loading="lazy" />
                        </div>
                    @endif
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-display text-base font-semibold text-slate-900 dark:text-white">{{ $project->title }}</h3>
                            @if ($project->status)
                                <span class="badge badge-brand shrink-0">{{ $project->status }}</span>
                            @endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (($project->tech ?? []) as $t)
                                <span class="badge badge-neutral">{{ $t }}</span>
                            @endforeach
                        </div>
                        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $project->description }}</p>
                        <div class="mt-5 flex items-center gap-3 pt-1">
                            <button
                                type="button"
                                class="btn btn-secondary btn-sm"
                                @click="$dispatch('open-project', @js([
                                    'title' => $project->title,
                                    'thumbnail' => $project->thumbnail,
                                    'status' => $project->status,
                                    'tech' => $project->tech ?? [],
                                    'description' => $project->description,
                                    'link' => $project->link,
                                ]))"
                            >
                                Details
                            </button>
                            @if ($project->link)
                                <a href="{{ $project->link }}" target="_blank" rel="noopener" class="nav-link inline-flex items-center gap-1 text-sm font-semibold text-brand-600 dark:text-brand-400">
                                    Live demo <x-icon name="arrow-up-right" class="icon h-3.5 w-3.5" />
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-slate-500 dark:text-slate-400">No projects configured yet.</p>
            @endforelse
        </div>

        @if ($projects->count() > $projectLimit)
            <div class="mt-10 flex flex-col items-center gap-2">
                <button type="button" wire:click="loadMoreProjects" wire:loading.attr="disabled" class="btn btn-secondary">
                    <x-icon name="plus" class="icon h-4 w-4" /> Load more work
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($projectLimit, $projectsTotal) }} of {{ $projectsTotal }} projects</p>
            </div>
        @endif
    </section>
    @endif

    @if (Setting::get('sections.services', true))
    <div>
    <section id="services" class="container-shell scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Services</p>
            <h2 class="section-heading mt-3">Services &amp; pricing</h2>
            <p class="section-lead mx-auto">Clear scopes, transparent pricing, and delivery you can rely on.</p>
        </div>
        <div class="card spotlight mx-auto mt-10 max-w-3xl px-6 py-10 text-center sm:px-10">
            <p class="mx-auto max-w-xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                Browse every category — Web Design, Web Dev, Marketing, SEO — and compare subscription plans
                with transparent pricing, instant ordering and secure payment.
            </p>
            <a href="{{ route('plan') }}" class="btn btn-primary btn-lg mt-6">
                Explore subscription plans
                <x-icon name="arrow-right" class="icon h-4 w-4" />
            </a>
        </div>
    </section>
    </div>
    @endif

    @if (Setting::get('sections.timeline', true))
    <section id="timeline" class="container-shell  scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Process</p>
            <h2 class="section-heading mt-3">How a project comes together</h2>
        </div>

        <div class="relative mx-auto mt-14 max-w-3xl">
            <div class="absolute bottom-0 left-4 top-0 w-px bg-gradient-to-b from-brand-300/60 via-brand-300/30 to-transparent sm:left-1/2 sm:-translate-x-px dark:from-brand-400/40 dark:via-brand-400/20" aria-hidden="true"></div>
            @forelse ($phases as $phase)
                <div class="relative mb-10 pl-12 last:mb-0 sm:w-1/2 sm:pl-0 {{ $loop->index % 2 === 1 ? 'sm:ml-auto sm:pl-12' : 'sm:pr-12 sm:text-right' }}">
                    <span class="absolute left-[13px] top-1 h-[7px] w-[7px] rounded-full bg-gradient-to-br from-brand-400 to-brand-600 shadow-[0_0_10px_rgb(99_102_241/0.6)] ring-4 ring-white/80 sm:left-auto dark:ring-slate-950 {{ $loop->index % 2 === 1 ? 'sm:-left-[3px]' : 'sm:-right-[3px]' }}" aria-hidden="true"></span>
                    <span class="badge badge-brand mb-2">{{ $phase->days }}</span>
                    <h3 class="font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $phase->title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $phase->description }}</p>
                </div>
            @empty
                <p class="text-slate-500 dark:text-slate-400">No timeline phases configured yet.</p>
            @endforelse
        </div>
    </section>
    @endif

    @if (\App\Support\Social::embed()['enabled'] && filled(\App\Support\Social::embed()['html']))
    <section id="social-feed" class="container-shell scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-4xl text-center">
            <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Social</p>
            <h2 class="section-heading mt-3">{{ \App\Support\Social::embed()['title'] ?: 'From the community' }}</h2>
        </div>
        <div class="mx-auto mt-10 max-w-3xl overflow-hidden rounded-3xl border border-white/50 bg-white/40 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
            <div class="flex min-h-40 items-center justify-center p-6">
                {!! \App\Support\Social::embed()['html'] !!}
            </div>
        </div>
    </section>
    @endif

    @if (Setting::get('sections.faq', true))
    <div>
    <section id="faq-section" class="container-shell scroll-mt-24 py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-3xl">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">FAQ</p>
                <h2 class="section-heading mt-3">Frequently asked questions</h2>
            </div>

            <div class="mt-12 space-y-3">
                @forelse ($faqs as $faq)
                    <div class="card overflow-hidden" x-data="{ open: false }">
                        <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                            <span class="font-display text-base font-semibold text-slate-900 dark:text-white">{{ $faq->question }}</span>
                            <x-icon name="chevron-down" class="icon h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition duration-200 ease-out"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition duration-150 ease-in"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            x-cloak
                            class="border-t border-slate-200/60 px-5 py-4 text-sm leading-relaxed text-slate-600 dark:border-white/10 dark:text-slate-400"
                        >
                            {{ $faq->answer }}
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 dark:text-slate-400">No FAQs configured yet.</p>
                @endforelse
            </div>
        </div>
    </section>
    </div>
    @endif

    <section class="container-shell pb-16 sm:pb-20 lg:pb-24">
        <div class="card spotlight relative overflow-hidden px-6 py-12 text-center sm:px-12 sm:py-16">
            <div class="pointer-events-none absolute -left-20 -top-24 h-64 w-64 rounded-full bg-brand-400/25 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-16 h-64 w-64 rounded-full bg-violet-400/20 blur-3xl" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-2xl">
                <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Let's build together</p>
                <h2 class="section-heading mt-3">Have a project in mind?</h2>
                <p class="section-lead mx-auto">From a quick landing page to a full production platform — tell me what you need and I'll handle the engineering.</p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="#services" class="btn btn-primary btn-lg">
                        Start an order
                        <x-icon name="arrow-right" class="icon h-4 w-4" />
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-secondary btn-lg">Send a message</a>
                </div>
            </div>
        </div>
    </section>
</div>
