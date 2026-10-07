<div>
    @if (count($this->reminders) > 0)
        <div class="mb-6 space-y-3">
            @foreach ($this->reminders as $reminder)
                <div class="card card-pad flex items-start gap-3 border-amber-300/60 bg-amber-500/5 dark:border-amber-400/20">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                        <x-icon name="bell" class="icon h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $reminder->title }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Reminder set for {{ $reminder->remind_at->format('M d, Y \a\t g:i A') }}
                            @if ($reminder->daysRemaining() !== null)
                                · {{ $reminder->isOverdue() ? 'Overdue' : $reminder->daysRemaining().' day(s) to deadline' }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('panel.projects.show', $reminder) }}" class="btn btn-secondary btn-sm shrink-0">View</a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Client portal</p>
            <h2 class="card-title mt-1">My Projects</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Live status, task checklists, feature progress, updates, downloads and budget offers.</p>
        </div>
        <a href="{{ route('panel.orders.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon h-4 w-4" /> Start a Project
        </a>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (['all' => 'All sources', 'client' => 'My order briefs', 'admin' => 'Team projects'] as $key => $label)
            <button
                type="button"
                wire:click="set('sourceFilter', '{{ $key }}')"
                class="badge cursor-pointer transition-colors {{ $sourceFilter === $key ? 'badge-brand' : 'badge-neutral' }}"
            >
                {{ $label }}
            </button>
        @endforeach
        <span class="mx-1 hidden h-4 w-px self-center bg-slate-300 dark:bg-white/10 sm:block"></span>
        @foreach (['all' => 'All statuses'] + \App\Models\ClientProject::STATUSES as $key => $label)
            <button
                type="button"
                wire:click="set('statusFilter', '{{ $key }}')"
                class="badge cursor-pointer transition-colors {{ $statusFilter === $key ? 'badge-brand' : 'badge-neutral' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($this->projects as $project)
            <a href="{{ route('panel.projects.show', $project) }}" class="card card-pad spotlight group relative overflow-hidden transition-transform duration-200 hover:-translate-y-0.5">
                <div class="pointer-events-none absolute -right-10 -top-12 h-32 w-32 rounded-full bg-brand-400/15 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate font-display text-base font-semibold text-slate-900 dark:text-white">{{ $project->title }}</h3>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                {{ $project->invoice?->itemLabel() ?? 'Custom order' }}
                            </p>
                        </div>
                        <span class="flex shrink-0 flex-col items-end gap-1">
                            @if ($project->isClientSubmitted())
                                <span class="badge badge-neutral">
                                    <x-icon name="send" class="icon h-3 w-3" /> Client brief
                                </span>
                            @endif
                            <span class="badge {{ $project->status === 'completed' ? 'badge-success' : ($project->status === 'cancelled' ? 'badge-danger' : ($project->status === 'in_progress' ? 'badge-brand' : 'badge-warning')) }}">
                                {{ $project->statusLabel() }}
                            </span>
                        </span>
                    </div>

                    @if ($project->description)
                        <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($project->description, 140) }}</p>
                    @endif

                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Progress</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $project->progress() }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-200/70 dark:bg-white/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700 transition-all duration-500" style="width: {{ $project->progress() }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1">
                            <x-icon name="activity" class="icon h-3.5 w-3.5" />
                            {{ ($project->tasks_count ?? 0) + ($project->features_count ?? 0) }} items
                        </span>
                        @if ($project->deadline)
                            <span class="flex items-center gap-1 {{ $project->isOverdue() ? 'font-semibold text-red-500' : '' }}">
                                <x-icon name="clock" class="icon h-3.5 w-3.5" />
                                {{ $project->deadline->format('M d, Y') }}
                                @if ($project->isOverdue()) · Overdue @endif
                            </span>
                        @endif
                        @if (($project->deliverables_count ?? 0) > 0)
                            <span class="flex items-center gap-1">
                                <x-icon name="file" class="icon h-3.5 w-3.5" />
                                {{ $project->deliverables_count }} deliverable(s)
                            </span>
                        @endif
                        @if (($project->media_count ?? 0) > 0)
                            <span class="flex items-center gap-1">
                                <x-icon name="image" class="icon h-3.5 w-3.5" />
                                {{ $project->media_count }} media
                            </span>
                        @endif
                        @if ($project->offerPending())
                            <span class="badge badge-warning">
                                <x-icon name="cash" class="icon h-3 w-3" /> Offer: ${{ number_format((float) $project->offer_price) }}
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="card card-pad py-12 text-center lg:col-span-2">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-500/10 text-slate-400">
                    <x-icon name="folder" class="icon h-6 w-6" />
                </span>
                <p class="mt-3 font-semibold text-slate-900 dark:text-white">No projects yet</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tell us about your project and we'll reply with an offer — or browse services to get started.</p>
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <a href="{{ route('panel.orders.create') }}" class="btn btn-primary btn-sm">Start a Project</a>
                    <a href="{{ route('home') }}#services" class="btn btn-secondary btn-sm">Browse services</a>
                </div>
            </div>
        @endforelse
    </div>
</div>
