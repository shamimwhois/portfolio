<div>
    @if ($detailId && $this->detail)
        {{-- ============================== DETAIL MANAGER ============================== --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button type="button" class="icon-btn" wire:click="closeDetail" aria-label="Back to list">
                    <x-icon name="arrow-left" class="icon h-4 w-4" />
                </button>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Client project</p>
                    <h2 class="card-title">{{ $this->detail->title }}</h2>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if ($this->detail->isClientSubmitted())
                    <span class="badge badge-neutral">
                        <x-icon name="send" class="icon h-3 w-3" /> Client brief
                    </span>
                @endif
                <span class="badge {{ $this->detail->status === 'completed' ? 'badge-success' : ($this->detail->status === 'cancelled' ? 'badge-danger' : ($this->detail->status === 'in_progress' ? 'badge-brand' : 'badge-warning')) }}">
                    {{ $this->detail->statusLabel() }}
                </span>
                <button type="button" class="btn btn-secondary btn-sm" wire:click="edit({{ $this->detail->id }})">
                    <x-icon name="edit" class="icon h-3.5 w-3.5" /> Edit
                </button>
            </div>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-pad">
                <p class="stat-label">Customer</p>
                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $this->detail->user?->name ?? '—' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $this->detail->user?->email }}</p>
                @if ($this->detail->isClientSubmitted())
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Submitted {{ $this->detail->submitted_at?->diffForHumans() }} · awaiting review</p>
                @endif
            </div>
            <div class="card card-pad">
                <p class="stat-label">Progress</p>
                <div class="mt-2 flex items-center gap-2">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200/70 dark:bg-white/10">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700 transition-all duration-500" style="width: {{ $this->detail->progress() }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $this->detail->progress() }}%</span>
                </div>
            </div>
            <div class="card card-pad">
                <p class="stat-label">Timeline</p>
                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                    {{ $this->detail->start_date?->format('M d, Y') ?? '—' }}
                    <span class="text-slate-400">→</span>
                    {{ $this->detail->deadline?->format('M d, Y') ?? '—' }}
                </p>
                @if ($this->detail->isOverdue())
                    <p class="text-xs font-medium text-red-500">Overdue by {{ abs($this->detail->daysRemaining()) }} day(s)</p>
                @elseif ($this->detail->daysRemaining() !== null)
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $this->detail->daysRemaining() }} day(s) left</p>
                @endif
            </div>
            <div class="card card-pad">
                <p class="stat-label">Counts</p>
                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                    {{ $this->detail->tasks->count() + $this->detail->features->count() }} items
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $this->detail->tasks->filter(fn ($t) => $t->is_complete)->count() + $this->detail->features->filter(fn ($f) => $f->is_complete)->count() }} done
                </p>
            </div>
        </div>

        @if ($showForm)
            <x-admin.modal title="{{ $editingId ? 'Edit Client Project' : 'New Client Project' }}" subtitle="Track delivery — status, docs, tasks and files" icon="briefcase" size="xl" close-method="cancel">
                @include('livewire.admin.partials.client-project-form')
                <x-slot:footer>
                    <x-admin.modal-actions form-id="client-project-form" save-label="{{ $editingId ? 'Save changes' : 'Create project' }}" />
                </x-slot:footer>
            </x-admin.modal>
        @endif

        {{-- Client brief (from the order form) --}}
        @if ($this->detail->isClientSubmitted() && ($this->detail->example_url || $this->detail->demo_url || $this->detail->design_notes || count($this->detail->color_palette ?? []) > 0 || $this->detail->budgetLabel() || $this->detail->timeline_preference || $this->detail->preferred_deadline))
            <section class="card card-pad mb-6">
                <div class="flex items-center gap-2">
                    <x-icon name="briefcase" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                    <h2 class="card-title">Customer brief</h2>
                    @if ($this->detail->package)
                        <span class="badge badge-neutral">Package: {{ $this->detail->package->title }}</span>
                    @endif
                    @if ($this->detail->tier)
                        <span class="badge badge-neutral">Service: {{ $this->detail->tier->name }}</span>
                    @endif
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
                                    · ideal {{ $this->detail->preferred_deadline->format('M d, Y') }}
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

        {{-- Budget negotiation --}}
        @if ($this->detail->isClientSubmitted())
        <section class="card card-pad mb-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <x-icon name="cash" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                    <h2 class="card-title">Budget negotiation</h2>
                    @if ($this->detail->offerPending())
                        <span class="badge badge-warning">Offer pending — ${{ number_format((float) $this->detail->offer_price) }}</span>
                    @elseif ($this->detail->offerAccepted())
                        <span class="badge badge-success">Accepted — ${{ number_format((float) $this->detail->offer_price) }}</span>
                    @elseif ($this->detail->offerDeclined())
                        <span class="badge badge-danger">Declined by customer</span>
                    @endif
                </div>
                @if ($this->detail->hasOffer())
                    <button type="button" wire:click="clearOffer" wire:confirm="Clear the current offer?" class="btn btn-ghost btn-sm">Clear offer</button>
                @endif
            </div>
            @if ($this->detail->offer_note)
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $this->detail->offer_note }}</p>
            @endif
            <form wire:submit="negotiateBudget" class="mt-4 grid gap-3 sm:grid-cols-[160px_1fr_auto]">
                <div>
                    <label for="offerPrice" class="label">Offer price (USD)</label>
                    <input id="offerPrice" type="number" wire:model="offerPrice" min="0" step="50" class="input" placeholder="900" />
                    @error('offerPrice') <p class="error-text mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="offerNote" class="label">Note to customer (optional)</label>
                    <input id="offerNote" type="text" wire:model="offerNote" class="input" placeholder="e.g. This price includes two revision rounds…" maxlength="1000" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <x-icon name="send" class="icon h-3.5 w-3.5" /> Send offer
                    </button>
                </div>
            </form>
        </section>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-2">
            <div class="space-y-6">
                {{-- Tasks --}}
                <section class="card card-pad">
                    <h2 class="card-title">Task List</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Work items — the customer sees these checked off live.</p>
                    <form wire:submit="addTask" class="mt-4 flex gap-2">
                        <input type="text" wire:model="taskTitle" class="input" placeholder="Add a task..." />
                        <button type="submit" class="btn btn-primary btn-sm shrink-0">Add</button>
                    </form>
                    @error('taskTitle') <p class="error-text mt-1">{{ $message }}</p> @enderror
                    <div class="mt-4 space-y-1.5">
                        @forelse ($this->detail->tasks as $task)
                            <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/40 px-3 py-2 dark:border-white/5 dark:bg-white/5">
                                <button type="button" wire:click="toggleTask({{ $task->id }})" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border transition-colors {{ $task->is_complete ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 text-transparent hover:border-brand-500 dark:border-white/20' }}">
                                    <x-icon name="check" class="icon h-3 w-3" />
                                </button>
                                <span class="flex-1 text-sm {{ $task->is_complete ? 'text-slate-400 line-through' : 'text-slate-800 dark:text-slate-200' }}">{{ $task->title }}</span>
                                <button type="button" wire:click="deleteTask({{ $task->id }})" class="text-slate-400 transition-colors hover:text-red-500" aria-label="Delete task">
                                    <x-icon name="trash" class="icon h-3.5 w-3.5" />
                                </button>
                            </div>
                        @empty
                            <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">No tasks yet.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Features --}}
                <section class="card card-pad">
                    <h2 class="card-title">Feature Checklist</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Deliverable features — complete / incomplete tracking.</p>
                    <form wire:submit="addFeature" class="mt-4 flex gap-2">
                        <input type="text" wire:model="featureTitle" class="input" placeholder="Add a feature..." />
                        <button type="submit" class="btn btn-primary btn-sm shrink-0">Add</button>
                    </form>
                    @error('featureTitle') <p class="error-text mt-1">{{ $message }}</p> @enderror
                    <div class="mt-4 space-y-1.5">
                        @forelse ($this->detail->features as $feature)
                            <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/40 px-3 py-2 dark:border-white/5 dark:bg-white/5">
                                <button type="button" wire:click="toggleFeature({{ $feature->id }})" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border transition-colors {{ $feature->is_complete ? 'border-brand-500 bg-brand-500 text-white' : 'border-slate-300 text-transparent hover:border-brand-500 dark:border-white/20' }}">
                                    <x-icon name="check" class="icon h-3 w-3" />
                                </button>
                                <span class="flex-1 text-sm {{ $feature->is_complete ? 'text-slate-400 line-through' : 'text-slate-800 dark:text-slate-200' }}">{{ $feature->title }}</span>
                                <button type="button" wire:click="deleteFeature({{ $feature->id }})" class="text-slate-400 transition-colors hover:text-red-500" aria-label="Delete feature">
                                    <x-icon name="trash" class="icon h-3.5 w-3.5" />
                                </button>
                            </div>
                        @empty
                            <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">No features yet.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                {{-- Update log --}}
                <section class="card card-pad">
                    <h2 class="card-title">Update Log</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Milestones, notes and reminders shown to the customer.</p>
                    <form wire:submit="postUpdate" class="mt-4 space-y-2">
                        <textarea wire:model="updateMessage" rows="2" class="input" placeholder="What changed?"></textarea>
                        <div class="flex flex-wrap items-center gap-2">
                            <select wire:model="updateType" class="input !w-auto">
                                <option value="update">Update</option>
                                <option value="milestone">Milestone</option>
                                <option value="note">Note</option>
                                <option value="reminder">Reminder</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Post</button>
                        </div>
                    </form>
                    @error('updateMessage') <p class="error-text mt-1">{{ $message }}</p> @enderror
                    <div class="mt-4 space-y-3">
                        @forelse ($this->detail->updates as $update)
                            <div class="flex items-start gap-3">
                                <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold uppercase {{ $update->type === 'milestone' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : ($update->type === 'reminder' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-brand-500/15 text-brand-700 dark:text-brand-300') }}">
                                    {{ $update->type === 'milestone' ? 'M' : ($update->type === 'reminder' ? 'R' : ($update->type === 'note' ? 'N' : 'U')) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="whitespace-pre-line text-sm text-slate-800 dark:text-slate-200">{{ $update->message }}</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                                        {{ $update->author?->name ?? 'System' }} · {{ $update->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <button type="button" wire:click="deleteUpdate({{ $update->id }})" class="text-slate-400 transition-colors hover:text-red-500" aria-label="Delete update">
                                    <x-icon name="trash" class="icon h-3.5 w-3.5" />
                                </button>
                            </div>
                        @empty
                            <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">No updates yet.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Deliverables --}}
                <section class="card card-pad">
                    <h2 class="card-title">Deliverables</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Upload files — the customer downloads these from their panel.</p>
                    <form wire:submit="uploadFile" class="mt-4 space-y-2">
                        <input type="file" wire:model="fileUpload" class="input !p-2 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-500/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700 dark:file:text-brand-300" />
                        <div class="flex items-center gap-2">
                            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                <span wire:loading.remove wire:target="uploadFile"><x-icon name="upload" class="icon h-3.5 w-3.5" /> Upload</span>
                                <span wire:loading wire:target="uploadFile">Uploading...</span>
                            </button>
                            @error('fileUpload') <span class="error-text">{{ $message }}</span> @enderror
                        </div>
                    </form>
                    <div class="mt-4 space-y-1.5">
                        @forelse ($this->detail->deliverables as $file)
                            <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/40 px-3 py-2 dark:border-white/5 dark:bg-white/5">
                                <x-icon name="file" class="icon h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                <span class="min-w-0 flex-1 truncate text-sm text-slate-800 dark:text-slate-200">{{ $file->name }}</span>
                                <span class="shrink-0 text-xs text-slate-400">{{ $file->humanSize() }}</span>
                                <button type="button" wire:click="deleteFile({{ $file->id }})" wire:confirm="Delete this file?" class="text-slate-400 transition-colors hover:text-red-500" aria-label="Delete file">
                                    <x-icon name="trash" class="icon h-3.5 w-3.5" />
                                </button>
                            </div>
                        @empty
                            <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">No deliverables uploaded yet.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Customer materials --}}
                <section class="card card-pad">
                    <h2 class="card-title">Customer materials</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Images, videos and documents the customer attached to their brief.</p>
                    @if ($this->detail->media->count() > 0)
                        <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
                            @foreach ($this->detail->media as $media)
                                <div class="group relative">
                                    @if ($media->isImage())
                                        <img src="{{ route('panel.projects.media', [$this->detail, $media]) }}" alt="Submitted image" class="h-20 w-full rounded-lg object-cover" />
                                    @else
                                        <div class="flex h-20 w-full items-center justify-center rounded-lg bg-slate-900 text-white">
                                            <x-icon name="video" class="icon h-6 w-6" />
                                        </div>
                                    @endif
                                    <button type="button" wire:click="deleteMedia({{ $media->id }})" wire:confirm="Delete this media file?" class="absolute -right-1.5 -top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow transition-opacity group-hover:opacity-100" aria-label="Delete media">
                                        <x-icon name="close" class="icon h-3 w-3" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4 space-y-1.5">
                        @forelse ($this->detail->attachments as $file)
                            <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/40 px-3 py-2 dark:border-white/5 dark:bg-white/5">
                                <x-icon name="file" class="icon h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                <span class="min-w-0 flex-1 truncate text-sm text-slate-800 dark:text-slate-200">{{ $file->name }}</span>
                                <span class="shrink-0 text-xs text-slate-400">{{ $file->humanSize() }}</span>
                                <button type="button" wire:click="deleteFile({{ $file->id }})" wire:confirm="Delete this attachment?" class="text-slate-400 transition-colors hover:text-red-500" aria-label="Delete attachment">
                                    <x-icon name="trash" class="icon h-3.5 w-3.5" />
                                </button>
                            </div>
                        @empty
                            @if ($this->detail->media->count() === 0)
                                <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">No materials submitted yet.</p>
                            @endif
                        @endforelse
                    </div>
                </section>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- Client docs --}}
            @php
                $docs = [
                    'requirements' => ['label' => 'Requirements', 'icon' => 'doc'],
                    'agreement' => ['label' => 'Agreement', 'icon' => 'doc'],
                    'policy' => ['label' => 'Policy', 'icon' => 'shield'],
                    'cancel_policy' => ['label' => 'Cancellation Policy', 'icon' => 'close'],
                    'return_policy' => ['label' => 'Return Policy', 'icon' => 'refresh'],
                ];
            @endphp
            @foreach ($docs as $key => $doc)
                <section class="card card-pad">
                    <div class="flex items-center gap-2">
                        <x-icon :name="$doc['icon']" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                        <h2 class="card-title">{{ $doc['label'] }}</h2>
                    </div>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        {{ $this->detail->{$key} ? \Illuminate\Support\Str::limit($this->detail->{$key}, 320) : 'Not set yet — edit the project to add this document.' }}
                    </p>
                    <button type="button" wire:click="edit({{ $this->detail->id }})" class="mt-3 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Edit document →
                    </button>
                </section>
            @endforeach
        </div>
    @else
        {{-- ============================== LIST ============================== --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="card-title">Client Projects</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tracked work delivered to customers — status, tasks, features and files.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm" wire:click="create">
                <x-icon name="plus" class="icon h-4 w-4" /> New Client Project
            </button>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach (['all' => 'All sources', 'client' => 'Client briefs', 'admin' => 'Team projects'] as $key => $label)
                <button
                    type="button"
                    wire:click="set('sourceFilter', '{{ $key }}')"
                    class="badge cursor-pointer transition-all {{ $sourceFilter === $key ? 'badge-brand' : 'badge-neutral' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
            <span class="mx-1 hidden h-4 w-px self-center bg-slate-300 dark:bg-white/10 sm:block"></span>
            @foreach (['all' => 'All statuses'] + \App\Models\ClientProject::STATUSES as $key => $label)
                <button
                    type="button"
                    wire:click="set('statusFilter', '{{ $key }}')"
                    class="badge cursor-pointer transition-all {{ $statusFilter === $key ? 'badge-brand' : 'badge-neutral' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($showForm)
            <x-admin.modal title="{{ $editingId ? 'Edit Client Project' : 'New Client Project' }}" subtitle="Track delivery — status, docs, tasks and files" icon="briefcase" size="xl" close-method="cancel">
                @include('livewire.admin.partials.client-project-form')
                <x-slot:footer>
                    <x-admin.modal-actions form-id="client-project-form" save-label="{{ $editingId ? 'Save changes' : 'Create project' }}" />
                </x-slot:footer>
            </x-admin.modal>
        @endif

        <section class="table-shell">
            <table class="table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->projects->take($this->limit) as $project)
                        <tr>
                            <td>
                                <button type="button" wire:click="openDetail({{ $project->id }})" class="text-left">
                                    <p class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $project->title }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($project->description, 50) }}</p>
                                    @if ($project->isClientSubmitted())
                                        <span class="badge badge-neutral mt-1">Client brief</span>
                                    @endif
                                </button>
                            </td>
                            <td>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $project->user?->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $project->user?->email }}</p>
                            </td>
                            <td><span class="badge {{ $project->status === 'completed' ? 'badge-success' : ($project->status === 'cancelled' ? 'badge-danger' : ($project->status === 'in_progress' ? 'badge-brand' : 'badge-warning')) }}">{{ $project->statusLabel() }}</span></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-200/70 dark:bg-white/10">
                                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700" style="width: {{ $project->progress() }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $project->progress() }}%</span>
                                </div>
                            </td>
                            <td class="text-sm {{ $project->isOverdue() ? 'font-semibold text-red-500' : 'text-slate-600 dark:text-slate-300' }}">
                                {{ $project->deadline?->format('M d') ?? '—' }}
                                @if ($project->isOverdue())
                                    <span class="badge badge-danger ml-1">Overdue</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <x-admin.actions :view-method="'openDetail'" :view-id="$project->id" :edit="$project->id" :delete="$project->id" delete-message="Delete this client project? Tasks, files and updates will be removed too." />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">No client projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($this->hasMore)
            <div class="mt-6 flex flex-col items-center gap-2">
                <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                    <x-icon name="plus" class="icon h-4 w-4" /> Load more projects
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
            </div>
        @endif
    @endif
</div>
