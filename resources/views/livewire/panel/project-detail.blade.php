<div wire:poll.8s="render" x-data="{ confirmCancel: false }">
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('panel.projects') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <x-icon name="arrow-left" class="icon h-3.5 w-3.5" /> Back to projects
            </a>
            <h2 class="card-title mt-2">{{ $this->detail->title }}</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ $this->detail->invoice?->itemLabel() ?? 'Custom order' }} · Started {{ $this->detail->start_date?->format('M d, Y') ?? '—' }}
            </p>
        </div>
        <div class="flex flex-col items-end gap-2">
            @if ($this->detail->isEditableByCustomer())
                <div class="flex flex-wrap justify-end gap-2">
                    <a href="{{ route('panel.orders.edit', $this->detail) }}" class="btn btn-secondary btn-sm">
                        <x-icon name="edit" class="icon h-3.5 w-3.5" /> Edit brief
                    </a>
                    <button type="button" @click="confirmCancel = true" class="btn btn-danger btn-sm">
                        <x-icon name="trash" class="icon h-3.5 w-3.5" /> Cancel order
                    </button>
                </div>
            @endif
            <span class="badge {{ $this->detail->status === 'completed' ? 'badge-success' : ($this->detail->status === 'cancelled' ? 'badge-danger' : ($this->detail->status === 'in_progress' ? 'badge-brand' : 'badge-warning')) }}">
                <span class="relative mr-1.5 inline-flex h-1.5 w-1.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-current opacity-60"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-current"></span>
                </span>
                {{ $this->detail->statusLabel() }}
            </span>
            @if ($this->detail->isOverdue())
                <span class="badge badge-danger">Overdue</span>
            @endif
        </div>
    </div>

    {{-- Budget offer / negotiation --}}
    @if ($this->detail->hasOffer())
        <section class="card card-pad mb-6 spotlight relative overflow-hidden {{ $this->detail->offerAccepted() ? 'border-emerald-300/60' : ($this->detail->offerDeclined() ? 'border-amber-300/60' : 'border-brand-300/60') }}">
            <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full {{ $this->detail->offerAccepted() ? 'bg-emerald-400/20' : 'bg-brand-400/20' }} blur-3xl" aria-hidden="true"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $this->detail->offerAccepted() ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-brand-500/15 text-brand-600 dark:text-brand-400' }}">
                        <x-icon name="cash" class="icon h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ $this->detail->offerAccepted() ? 'Offer accepted' : ($this->detail->offerDeclined() ? 'Offer declined' : 'Budget offer from the team') }}
                        </p>
                        <p class="mt-0.5 font-display text-2xl font-bold text-slate-900 dark:text-white">${{ number_format((float) $this->detail->offer_price) }}</p>
                        @if ($this->detail->offer_note)
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $this->detail->offer_note }}</p>
                        @endif
                        @if ($this->detail->offerPending() && ($this->detail->budget_from !== null || $this->detail->budget_to !== null))
                            <p class="mt-1 text-xs text-slate-400">Your requested range: {{ $this->detail->budgetLabel() }}</p>
                        @endif
                    </div>
                </div>
                @if ($this->detail->offerPending())
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <button type="button" wire:click="acceptOffer" class="btn btn-primary btn-sm">
                            <x-icon name="check" class="icon h-3.5 w-3.5" /> Accept offer
                        </button>
                        <button type="button" wire:click="declineOffer" class="btn btn-secondary btn-sm">
                            Decline
                        </button>
                    </div>
                @elseif ($this->detail->offerDeclined())
                    <p class="shrink-0 text-xs text-slate-500 dark:text-slate-400">Awaiting a revised offer, or edit your brief.</p>
                @endif
            </div>
        </section>
    @endif

    @if ($this->detail->description)
        <section class="card card-pad mb-6">
            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $this->detail->description }}</p>
        </section>
    @endif

    {{-- Brief: references, design, budget, timeline --}}
    @if ($this->detail->example_url || $this->detail->demo_url || $this->detail->design_notes || count($this->detail->color_palette ?? []) > 0 || $this->detail->budgetLabel() || $this->detail->timeline_preference || $this->detail->preferred_deadline)
        <section class="card card-pad mb-6">
            <div class="flex items-center gap-2">
                <x-icon name="briefcase" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                <h3 class="card-title">Your brief</h3>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @if ($this->detail->example_url)
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-slate-400">Example website</p>
                        <a href="{{ $this->detail->example_url }}" target="_blank" rel="noopener" class="mt-1 flex items-center gap-1.5 break-all text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">
                            <x-icon name="link" class="icon h-3.5 w-3.5 shrink-0" /> {{ $this->detail->example_url }}
                        </a>
                    </div>
                @endif
                @if ($this->detail->demo_url)
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-slate-400">Existing site / demo</p>
                        <a href="{{ $this->detail->demo_url }}" target="_blank" rel="noopener" class="mt-1 flex items-center gap-1.5 break-all text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">
                            <x-icon name="globe" class="icon h-3.5 w-3.5 shrink-0" /> {{ $this->detail->demo_url }}
                        </a>
                    </div>
                @endif
                @if ($this->detail->timeline_preference || $this->detail->preferred_deadline)
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-slate-400">Timeline</p>
                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $this->detail->timelinePreferenceLabel() ?? 'No preference' }}
                            @if ($this->detail->preferred_deadline)
                                · ideal deadline {{ $this->detail->preferred_deadline->format('M d, Y') }}
                            @endif
                        </p>
                    </div>
                @endif
                @if ($this->detail->budgetLabel())
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-slate-400">Requested budget</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $this->detail->budgetLabel() }}</p>
                    </div>
                @endif
                @if ($this->detail->design_notes)
                    <div class="sm:col-span-2">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400">Design notes</p>
                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $this->detail->design_notes }}</p>
                    </div>
                @endif
                @if (count($this->detail->color_palette ?? []) > 0)
                    <div class="sm:col-span-2">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400">Brand colors</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($this->detail->color_palette as $color)
                                <span class="flex items-center gap-2 rounded-full border border-white/40 bg-white/40 py-1 pl-1 pr-2.5 text-xs font-medium text-slate-700 dark:border-white/10 dark:bg-white/10 dark:text-slate-200">
                                    <span class="h-6 w-6 rounded-full border border-black/10" style="background-color: {{ $color }}" aria-hidden="true"></span>
                                    {{ strtoupper($color) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Progress + timeline --}}
    <section class="card card-pad spotlight relative mb-6 overflow-hidden">
        <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Overall progress</h3>
                <span class="font-display text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $this->detail->progress() }}%</span>
            </div>
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-200/70 dark:bg-white/10">
                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 via-brand-600 to-emerald-500 transition-all duration-700" style="width: {{ $this->detail->progress() }}%"></div>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-white/40 bg-white/30 px-3 py-2.5 dark:border-white/5 dark:bg-white/5">
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">Start</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $this->detail->start_date?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-white/40 bg-white/30 px-3 py-2.5 dark:border-white/5 dark:bg-white/5">
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">Deadline</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $this->detail->deadline?->format('M d, Y') ?? '—' }}
                        @if ($this->detail->daysRemaining() !== null && ! $this->detail->isOverdue())
                            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">({{ $this->detail->daysRemaining() }} days)</span>
                        @endif
                    </p>
                </div>
                <div class="rounded-xl border border-white/40 bg-white/30 px-3 py-2.5 dark:border-white/5 dark:bg-white/5">
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">Done</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $this->detail->completedTasks()->count() + $this->detail->completedFeatures()->count() }} / {{ $this->detail->tasks()->count() + $this->detail->features()->count() }} items
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid items-start gap-6 lg:grid-cols-2">
        {{-- Task list --}}
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Task list</h3>
                <span class="badge badge-neutral">{{ $this->detail->completedTasks()->count() }}/{{ $this->detail->tasks()->count() }}</span>
            </div>
            <div class="mt-4 space-y-2">
                @forelse ($this->detail->tasks as $task)
                    <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/30 px-3 py-2 transition-colors dark:border-white/5 dark:bg-white/5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md {{ $task->is_complete ? 'bg-emerald-500 text-white' : 'border border-slate-300 dark:border-white/20' }}">
                            @if ($task->is_complete)
                                <x-icon name="check" class="icon h-3 w-3" />
                            @endif
                        </span>
                        <span class="text-sm {{ $task->is_complete ? 'text-slate-400 line-through' : 'text-slate-800 dark:text-slate-200' }}">{{ $task->title }}</span>
                    </div>
                @empty
                    <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">Task list is being prepared.</p>
                @endforelse
            </div>
        </section>

        {{-- Feature checklist --}}
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Features</h3>
                <span class="badge badge-neutral">{{ $this->detail->completedFeatures()->count() }}/{{ $this->detail->features()->count() }}</span>
            </div>
            <div class="mt-4 space-y-2">
                @forelse ($this->detail->features as $feature)
                    <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/30 px-3 py-2 transition-colors dark:border-white/5 dark:bg-white/5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md {{ $feature->is_complete ? 'bg-brand-500 text-white' : 'border border-slate-300 dark:border-white/20' }}">
                            @if ($feature->is_complete)
                                <x-icon name="check" class="icon h-3 w-3" />
                            @endif
                        </span>
                        <span class="text-sm {{ $feature->is_complete ? 'text-slate-400 line-through' : 'text-slate-800 dark:text-slate-200' }}">{{ $feature->title }}</span>
                    </div>
                @empty
                    <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">Feature list is being prepared.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-2">
        {{-- Update log --}}
        <section class="card card-pad">
            <h3 class="card-title">Project updates</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Milestones and notes from the team — refreshes automatically.</p>
            <div class="mt-4 space-y-4">
                @forelse ($this->detail->updates as $update)
                    <div class="relative flex gap-3">
                        <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold uppercase {{ $update->type === 'milestone' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : ($update->type === 'reminder' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-brand-500/15 text-brand-700 dark:text-brand-300') }}">
                            {{ $update->type === 'milestone' ? 'M' : ($update->type === 'reminder' ? 'R' : ($update->type === 'note' ? 'N' : 'U')) }}
                        </span>
                        <div class="min-w-0">
                            <p class="whitespace-pre-line text-sm leading-relaxed text-slate-800 dark:text-slate-200">{{ $update->message }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                                {{ $update->author?->name ?? 'Team' }} · {{ $update->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">No updates yet — the team will post progress here.</p>
                @endforelse
            </div>
        </section>

        {{-- Files (deliverables only) --}}
        <section class="card card-pad">
            <h3 class="card-title">Downloads</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Deliverables uploaded by the team.</p>
            <div class="mt-4 space-y-2">
                @forelse ($this->detail->deliverables as $file)
                    <a href="{{ route('panel.projects.file', [$this->detail, $file]) }}" class="group flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/30 px-3 py-2.5 transition-colors hover:border-brand-400/50 hover:bg-brand-500/5 dark:border-white/5 dark:bg-white/5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500/15 to-violet-500/15 text-brand-600 dark:text-brand-400">
                            <x-icon name="file" class="icon h-4 w-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-slate-900 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-300">{{ $file->name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $file->humanSize() }} · {{ $file->created_at->format('M d, Y') }}</span>
                        </span>
                        <x-icon name="download" class="icon h-4 w-4 shrink-0 text-slate-400 transition-colors group-hover:text-brand-600 dark:group-hover:text-brand-400" />
                    </a>
                @empty
                    <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">No files yet — deliverables will appear here.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Customer-submitted materials --}}
    @if ($this->detail->media->count() > 0 || $this->detail->attachments->count() > 0)
        <section class="card card-pad mt-6">
            <div class="flex items-center gap-2">
                <x-icon name="upload" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                <h3 class="card-title">Materials you submitted</h3>
            </div>
            @if ($this->detail->media->count() > 0)
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($this->detail->media as $media)
                        @if ($media->isImage())
                            <a href="{{ route('panel.projects.media', [$this->detail, $media]) }}" target="_blank" rel="noopener" class="group relative overflow-hidden rounded-xl border border-white/40 dark:border-white/5">
                                <img src="{{ route('panel.projects.media', [$this->detail, $media]) }}" alt="Submitted image" class="h-28 w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                            </a>
                        @else
                            <a href="{{ route('panel.projects.media', [$this->detail, $media]) }}" target="_blank" rel="noopener" class="group relative flex h-28 items-center justify-center overflow-hidden rounded-xl border border-white/40 bg-slate-900 dark:border-white/5">
                                <x-icon name="play" class="icon h-8 w-8 text-white/80 transition-transform duration-300 group-hover:scale-110" />
                                <span class="absolute bottom-2 left-2 rounded-full bg-slate-900/70 px-2 py-0.5 text-[10px] font-medium text-white">Video</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
            @if ($this->detail->attachments->count() > 0)
                <div class="mt-4 space-y-2">
                    @foreach ($this->detail->attachments as $file)
                        <a href="{{ route('panel.projects.file', [$this->detail, $file]) }}" class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/30 px-3 py-2.5 transition-colors hover:border-brand-400/50 hover:bg-brand-500/5 dark:border-white/5 dark:bg-white/5">
                            <x-icon name="file" class="icon h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                            <span class="min-w-0 flex-1 truncate text-sm text-slate-700 dark:text-slate-200">{{ $file->name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $file->humanSize() }} · {{ $file->created_at->format('M d, Y') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Project documents --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @php
            $docs = [
                'requirements' => ['label' => 'Project requirements', 'icon' => 'doc'],
                'agreement' => ['label' => 'Agreement', 'icon' => 'doc'],
                'policy' => ['label' => 'Project policy', 'icon' => 'shield'],
                'cancel_policy' => ['label' => 'Cancellation policy', 'icon' => 'close'],
                'return_policy' => ['label' => 'Return & refund policy', 'icon' => 'refresh'],
            ];
        @endphp
        @foreach ($docs as $key => $doc)
            <section class="card card-pad">
                <div class="flex items-center gap-2">
                    <x-icon :name="$doc['icon']" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                    <h3 class="card-title">{{ $doc['label'] }}</h3>
                </div>
                @if ($this->detail->{$key})
                    <div class="mt-2 max-h-48 overflow-y-auto whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $this->detail->{$key} }}</div>
                @else
                    <p class="mt-2 text-sm text-slate-400 dark:text-slate-500">Document not published yet.</p>
                @endif
            </section>
        @endforeach
    </div>

    {{-- Cancel order confirm modal --}}
    @if ($this->detail->isEditableByCustomer())
        <div x-cloak>
            <div
                x-show="confirmCancel"
                x-transition
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm"
                @click.self="confirmCancel = false"
            >
                <div class="glass-strong w-full max-w-sm rounded-2xl p-6 animate-modal-in">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-500/15 text-red-600 dark:text-red-400">
                        <x-icon name="trash" class="icon h-6 w-6" />
                    </span>
                    <h3 class="mt-4 text-center text-lg font-semibold">Cancel this order?</h3>
                    <p class="mt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                        Your brief will be marked as cancelled. You can always start a new project later.
                    </p>
                    <div class="mt-5 flex justify-center gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" @click="confirmCancel = false">Keep it</button>
                        <button type="button" wire:click="cancelOrder" class="btn btn-danger btn-sm">
                            <x-icon name="trash" class="icon h-3.5 w-3.5" /> Cancel order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
