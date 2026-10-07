<div>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('panel.projects') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <x-icon name="arrow-left" class="icon h-3.5 w-3.5" /> Back to projects
            </a>
            <h2 class="card-title mt-2">{{ $editingProjectId ? 'Edit your order' : 'Start a project' }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Tell us everything we need to quote accurately — the more detail, the better.
            </p>
        </div>
        @if ($editingProjectId)
            <span class="badge badge-warning">Pending order · editable</span>
        @endif
    </div>

    {{-- Stepper --}}
    <div class="mb-6 flex items-center gap-1 overflow-x-auto pb-1 sm:gap-2">
        @foreach ($this->stepMeta as $num => $meta)
            @php
                $active = $this->step === $num;
                $done = $this->step > $num;
            @endphp
            <button
                type="button"
                wire:click="goToStep({{ $num }})"
                @class([
                    'flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1.5 text-xs font-medium transition-colors',
                    'cursor-pointer' => $done,
                    'cursor-default' => ! $done,
                ])
            >
                <span
                    @class([
                        'flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold',
                        'bg-brand-600 text-white' => $active,
                        'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' => $done,
                        'bg-slate-200/70 text-slate-500 dark:bg-white/10 dark:text-slate-400' => ! $active && ! $done,
                    ])
                >
                    @if ($done)
                        <x-icon name="check" class="icon h-3 w-3" />
                    @else
                        {{ $num }}
                    @endif
                </span>
                <span @class([
                    'hidden sm:inline',
                    'text-brand-700 dark:text-brand-300' => $active,
                    'text-slate-400 line-through' => $done,
                    'text-slate-500 dark:text-slate-400' => ! $active && ! $done,
                ])>{{ $meta['label'] }}</span>
            </button>
            @if ($num < 6)
                <span class="h-px w-3 shrink-0 bg-slate-300 dark:bg-white/10 sm:w-5"></span>
            @endif
        @endforeach
    </div>

    <form wire:submit="handleSubmit" class="space-y-6">
        {{-- Step 1 — Basics --}}
        @if ($this->step === 1)
            <section class="card card-pad spotlight relative overflow-hidden">
                <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative space-y-4">
                    <div>
                        <h3 class="card-title">Project basics</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">What are we building together?</p>
                    </div>

                    <div>
                        <label for="title" class="label">Project title <span class="text-red-500">*</span></label>
                        <input id="title" type="text" wire:model="title" class="input" placeholder="e.g. Brand refresh & new landing page" maxlength="190" />
                        @error('title') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="label">Project description</label>
                        <textarea id="description" wire:model="description" rows="4" class="input" placeholder="Give us a short overview of what you need — goals, audience, scale." maxlength="2000"></textarea>
                        @error('description') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="packageId" class="label">Related package (optional)</label>
                            <select id="packageId" wire:model="packageId" class="input">
                                <option value="">No package — custom project</option>
                                @foreach ($this->packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->categoryLabel() }} // {{ $package->title }} — {{ $package->displayPrice() }}</option>
                                @endforeach
                            </select>
                            @error('packageId') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tierId" class="label">Related service (optional)</label>
                            <select id="tierId" wire:model="tierId" class="input">
                                <option value="">No service selected</option>
                                @foreach ($this->tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->category?->name }} // {{ $tier->name }}</option>
                                @endforeach
                            </select>
                            @error('tierId') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="timelinePreference" class="label">Preferred timeline</label>
                            <select id="timelinePreference" wire:model="timelinePreference" class="input">
                                <option value="">No preference</option>
                                @foreach (\App\Models\ClientProject::TIMELINE_PREFERENCES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('timelinePreference') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="preferredDeadline" class="label">Ideal deadline (optional)</label>
                            <input id="preferredDeadline" type="date" wire:model="preferredDeadline" class="input" />
                            @error('preferredDeadline') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Step 2 — Requirements --}}
        @if ($this->step === 2)
            <section class="card card-pad spotlight relative overflow-hidden">
                <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-violet-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <h3 class="card-title">Project requirements</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Be specific: pages/screens, features, integrations, content, hosting, admin needs. Numbered lists are easiest to review.
                    </p>
                    <div class="mt-4">
                        <textarea wire:model="requirements" rows="12" class="input font-mono text-sm" placeholder="1. Landing page with hero, services, testimonials&#10;2. Contact form that emails me&#10;3. Blog with categories&#10;4. Mobile responsive throughout" maxlength="5000"></textarea>
                        <div class="mt-1 flex items-center justify-between">
                            <div>
                                @error('requirements') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                            <span class="text-[11px] text-slate-400">{{ strlen($this->requirements) }}/5000</span>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Step 3 — References & design --}}
        @if ($this->step === 3)
            <section class="card card-pad spotlight relative overflow-hidden">
                <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-rose-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative space-y-4">
                    <div>
                        <h3 class="card-title">References & design direction</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Websites you love, demos to match, and the look & feel you're after.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="exampleUrl" class="label">Example website (optional)</label>
                            <input id="exampleUrl" type="url" wire:model="exampleUrl" class="input" placeholder="https://site-you-love.com" />
                            @error('exampleUrl') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="demoUrl" class="label">Existing site / demo (optional)</label>
                            <input id="demoUrl" type="url" wire:model="demoUrl" class="input" placeholder="https://current-site.com" />
                            @error('demoUrl') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="designNotes" class="label">Design notes (optional)</label>
                        <textarea id="designNotes" wire:model="designNotes" rows="4" class="input" placeholder="Mood, vibe, must-haves, things to avoid, inspiration direction..." maxlength="2000"></textarea>
                        @error('designNotes') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Brand colors (optional, up to 5)</label>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($this->colorPalette as $index => $color)
                                <span class="group flex items-center gap-2 rounded-full border border-white/40 bg-white/40 py-1 pl-1 pr-2.5 text-xs font-medium text-slate-700 dark:border-white/10 dark:bg-white/10 dark:text-slate-200">
                                    <span class="h-6 w-6 rounded-full border border-black/10" style="background-color: {{ $color }}" aria-hidden="true"></span>
                                    {{ strtoupper($color) }}
                                    <button type="button" wire:click="removeColor({{ $index }})" class="text-slate-400 transition-colors hover:text-red-500" aria-label="Remove {{ $color }}">
                                        <x-icon name="close" class="icon h-3 w-3" />
                                    </button>
                                </span>
                            @endforeach
                            @if (count($this->colorPalette) < 5)
                                <span class="flex items-center gap-1.5">
                                    <input type="color" wire:model.live="colorInput" class="h-9 w-10 cursor-pointer rounded-lg border border-white/40 bg-transparent p-1 dark:border-white/10" aria-label="Pick a color" />
                                    <input type="text" wire:model="colorInput" class="input w-28" placeholder="#6366f1" maxlength="7" />
                                    <button type="button" wire:click="addColor" class="btn btn-secondary btn-sm">
                                        <x-icon name="plus" class="icon h-3.5 w-3.5" /> Add
                                    </button>
                                </span>
                            @endif
                        </div>
                        @error('colorInput') <p class="error-text">{{ $message }}</p> @enderror
                        @error('colorPalette') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        @endif

        {{-- Step 4 — Budget --}}
        @if ($this->step === 4)
            <section class="card card-pad spotlight relative overflow-hidden">
                <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-emerald-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <h3 class="card-title">Budget</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        A range helps us scope the right plan. Don't worry — we'll send a firm offer you can accept, decline, or negotiate before any work starts.
                    </p>
                    <div class="mt-4 grid max-w-md gap-4 sm:grid-cols-2">
                        <div>
                            <label for="budgetFrom" class="label">From (USD)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-400">$</span>
                                <input id="budgetFrom" type="number" wire:model="budgetFrom" min="0" step="50" class="input pl-7" placeholder="500" />
                            </div>
                            @error('budgetFrom') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="budgetTo" class="label">To (USD)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-400">$</span>
                                <input id="budgetTo" type="number" wire:model="budgetTo" min="0" step="50" class="input pl-7" placeholder="1200" />
                            </div>
                            @error('budgetTo') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Step 5 — Materials --}}
        @if ($this->step === 5)
            <section class="card card-pad spotlight relative overflow-hidden">
                <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-amber-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative space-y-5">
                    <div>
                        <h3 class="card-title">Materials & attachments</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Share anything that helps: brand assets, wireframes, photos, reference videos.</p>
                    </div>

                    @php
                        $wildErrors = collect($errors->getMessages());
                        $docErrors = $wildErrors->filter(fn ($m, $k) => str_starts_with($k, 'docs.'));
                        $imageErrors = $wildErrors->filter(fn ($m, $k) => str_starts_with($k, 'images.'));
                    @endphp

                    {{-- Existing materials (edit mode) --}}
                    @if ($this->editingProjectId && ($this->existingMedia->count() > 0 || $this->existingDocs->count() > 0))
                        <div class="rounded-xl border border-white/40 bg-white/30 p-4 dark:border-white/5 dark:bg-white/5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Already attached</p>
                            @if ($this->existingMedia->count() > 0)
                                <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
                                    @foreach ($this->existingMedia as $media)
                                        <div class="group relative">
                                            @if ($media->isImage())
                                                <img src="{{ route('panel.projects.media', [$this->editingProject, $media]) }}" alt="Attachment" class="h-20 w-full rounded-lg object-cover" />
                                            @else
                                                <div class="flex h-20 w-full items-center justify-center rounded-lg bg-slate-900 text-white">
                                                    <x-icon name="video" class="icon h-6 w-6" />
                                                </div>
                                            @endif
                                            <button type="button" wire:click="removeExistingMedia({{ $media->id }})" class="absolute -right-1.5 -top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow transition-opacity group-hover:opacity-100" aria-label="Remove">
                                                <x-icon name="close" class="icon h-3 w-3" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($this->existingDocs->count() > 0)
                                <div class="mt-3 space-y-2">
                                    @foreach ($this->existingDocs as $file)
                                        <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/40 px-3 py-2 dark:border-white/5 dark:bg-white/5">
                                            <x-icon name="file" class="icon h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                            <span class="min-w-0 flex-1 truncate text-sm text-slate-700 dark:text-slate-200">{{ $file->name }}</span>
                                            <span class="text-[11px] text-slate-400">{{ $file->humanSize() }}</span>
                                            <button type="button" wire:click="removeExistingDoc({{ $file->id }})" class="text-slate-400 transition-colors hover:text-red-500" aria-label="Remove {{ $file->name }}">
                                                <x-icon name="trash" class="icon h-4 w-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Docs --}}
                    <div>
                        <label class="label">Documents (PDF, DOCX, TXT, MD — up to 5, 20MB each)</label>
                        <label class="upload-zone" for="docs">
                            <input id="docs" type="file" wire:model="docs" multiple class="sr-only" accept=".pdf,.doc,.docx,.txt,.md,.rtf,.odt" />
                            <x-icon name="upload" class="icon mx-auto h-6 w-6 text-slate-400" />
                            <span class="mt-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Click to upload documents</span>
                            <span class="mt-0.5 block text-[11px] text-slate-400">Requirements docs, brand guidelines, contracts...</span>
                        </label>
                        @if ($docErrors->isNotEmpty())
                            <p class="error-text">{{ $docErrors->flatten()->first() }}</p>
                        @elseif (count($this->docs) > 0)
                            <div class="mt-2 space-y-2">
                                @foreach ($this->docs as $index => $doc)
                                    <div class="flex items-center gap-2.5 rounded-lg border border-white/40 bg-white/40 px-3 py-2 dark:border-white/5 dark:bg-white/5">
                                        <x-icon name="file" class="icon h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                                        <span class="min-w-0 flex-1 truncate text-sm text-slate-700 dark:text-slate-200">{{ $doc->getClientOriginalName() }}</span>
                                        <span class="text-[11px] text-slate-400">{{ number_format($doc->getSize() / 1024, 0) }} KB</span>
                                        <span wire:key="doc-{{ $index }}" wire:loading.class="opacity-40" wire:target="docs" class="text-[11px] text-brand-600">Uploading…</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Images --}}
                    <div>
                        <label class="label">Images (JPG, PNG, WebP, GIF — up to 6, 10MB each)</label>
                        <label class="upload-zone" for="images">
                            <input id="images" type="file" wire:model="images" multiple class="sr-only" accept="image/jpeg,image/png,image/webp,image/gif" />
                            <x-icon name="image" class="icon mx-auto h-6 w-6 text-slate-400" />
                            <span class="mt-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Click to upload images</span>
                            <span class="mt-0.5 block text-[11px] text-slate-400">Wireframes, brand assets, reference screenshots...</span>
                        </label>
                        @if ($imageErrors->isNotEmpty())
                            <p class="error-text">{{ $imageErrors->flatten()->first() }}</p>
                        @elseif (count($this->images) > 0)
                            <div class="mt-2 grid grid-cols-3 gap-3 sm:grid-cols-4">
                                @foreach ($this->images as $index => $image)
                                    <div class="group relative">
                                        <img src="{{ $image->temporaryUrl() }}" alt="Upload preview" class="h-24 w-full rounded-lg object-cover" />
                                        <span wire:key="img-{{ $index }}" wire:loading.class="opacity-40" wire:target="images" class="absolute inset-0 flex items-center justify-center rounded-lg bg-slate-900/40 text-[10px] font-medium text-white">Uploading…</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Video --}}
                    <div>
                        <label class="label">Video (MP4, WebM, OGG, MOV — one, 64MB max)</label>
                        <label class="upload-zone" for="video">
                            <input id="video" type="file" wire:model="video" class="sr-only" accept="video/mp4,video/webm,video/ogg,video/quicktime" />
                            <x-icon name="video" class="icon mx-auto h-6 w-6 text-slate-400" />
                            <span class="mt-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Click to upload a video</span>
                            <span class="mt-0.5 block text-[11px] text-slate-400">Walkthroughs, screen recordings, motion references...</span>
                        </label>
                        @error('video') <p class="error-text">{{ $message }}</p> @enderror
                        @if ($this->video)
                            <div class="mt-2 overflow-hidden rounded-lg border border-white/40 bg-slate-900 dark:border-white/10">
                                <video src="{{ $this->video->temporaryUrl() }}" controls class="w-full" style="max-height: 220px"></video>
                                <p class="border-t border-white/10 px-3 py-2 text-xs text-slate-300">{{ $this->video->getClientOriginalName() }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- Step 6 — Review --}}
        @if ($this->step === 6)
            <section class="card card-pad spotlight relative overflow-hidden">
                <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <h3 class="card-title">Review & submit</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Double-check your brief before sending it to the team.</p>

                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Title</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $this->title }}</dd>
                        </div>
                        @if ($this->description)
                            <div class="sm:col-span-2">
                                <dt class="text-[11px] uppercase tracking-wider text-slate-400">Description</dt>
                                <dd class="mt-0.5 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $this->description }}</dd>
                            </div>
                        @endif
                        @if ($this->packageId || $this->tierId)
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-slate-400">Related package</dt>
                                <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ $this->packages->firstWhere('id', $this->packageId)?->title ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-slate-400">Related service</dt>
                                <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ $this->tiers->firstWhere('id', $this->tierId)?->name ?? '—' }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Timeline</dt>
                            <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ \App\Models\ClientProject::TIMELINE_PREFERENCES[$this->timelinePreference] ?? 'No preference' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Ideal deadline</dt>
                            <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ $this->preferredDeadline ? \Carbon\Carbon::parse($this->preferredDeadline)->format('M d, Y') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Budget range</dt>
                            <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">
                                @if ($this->budgetFrom || $this->budgetTo)
                                    ${{ number_format((float) ($this->budgetFrom ?: 0)) }} – ${{ number_format((float) ($this->budgetTo ?: 0)) }}
                                @else
                                    Open — awaiting your offer
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Requirements</dt>
                            <dd class="mt-0.5 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $this->requirements }}</dd>
                        </div>
                        @if ($this->exampleUrl || $this->demoUrl || $this->designNotes || count($this->colorPalette) > 0)
                            <div class="sm:col-span-2">
                                <dt class="text-[11px] uppercase tracking-wider text-slate-400">References & design</dt>
                                <dd class="mt-1 space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                    @if ($this->exampleUrl) <p class="flex items-center gap-1.5"><x-icon name="link" class="icon h-3.5 w-3.5 text-brand-500" /> Example: <span class="break-all text-slate-700 dark:text-slate-200">{{ $this->exampleUrl }}</span></p> @endif
                                    @if ($this->demoUrl) <p class="flex items-center gap-1.5"><x-icon name="globe" class="icon h-3.5 w-3.5 text-brand-500" /> Demo: <span class="break-all text-slate-700 dark:text-slate-200">{{ $this->demoUrl }}</span></p> @endif
                                    @if ($this->designNotes) <p class="whitespace-pre-line">{{ $this->designNotes }}</p> @endif
                                    @if (count($this->colorPalette) > 0)
                                        <p class="flex items-center gap-1.5">
                                            Colors:
                                            @foreach ($this->colorPalette as $color)
                                                <span class="inline-block h-4 w-4 rounded-full border border-black/10" style="background-color: {{ $color }}" title="{{ $color }}" aria-label="{{ $color }}"></span>
                                            @endforeach
                                        </p>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Documents</dt>
                            <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ count($this->docs) }} file(s) @if ($this->editingProjectId) + {{ $this->existingDocs->count() }} attached @endif</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-slate-400">Images & video</dt>
                            <dd class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">{{ count($this->images) }} image(s), {{ $this->video ? '1 video' : 'no video' }} @if ($this->editingProjectId) + {{ $this->existingMedia->count() }} attached @endif</dd>
                        </div>
                    </dl>

                    <div class="mt-6 rounded-xl border border-brand-200 bg-brand-500/5 px-4 py-3 text-xs text-slate-600 dark:border-brand-500/20 dark:text-slate-300">
                        By submitting, the team will review your brief and reply with a price offer in your panel. You can accept, decline, or counter — no work starts until you agree.
                    </div>
                </div>
            </section>
        @endif

        {{-- Footer nav --}}
        <div class="flex items-center justify-between gap-3">
            <button type="button" wire:click="prevStep" @class(['btn btn-secondary', 'invisible' => $this->step === 1])>
                <x-icon name="arrow-left" class="icon h-4 w-4" /> Back
            </button>

            @if ($this->step < 6)
                <button type="button" wire:click="nextStep" class="btn btn-primary">
                    Continue <x-icon name="arrow-right" class="icon h-4 w-4" />
                </button>
            @else
                <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="btn btn-primary">
                    <span wire:loading.remove wire:target="submit">
                        <x-icon name="send" class="icon h-4 w-4" /> {{ $editingProjectId ? 'Save changes' : 'Submit order' }}
                    </span>
                    <span wire:loading wire:target="submit">Submitting…</span>
                </button>
            @endif
        </div>
    </form>
</div>
