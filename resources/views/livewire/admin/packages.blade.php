<div>
    <div class="mb-6 flex items-center justify-between">
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New Package
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit Package' : 'New Package' }}" subtitle="Sell reusable tech kits with install commands" icon="box" size="xl" close-method="cancel">
            <form wire:submit="save" id="package-form" class="grid gap-4 md:grid-cols-2">
                <x-admin.field label="Category" name="category" type="select" icon="folder" wire:model="category">
                    @foreach (\App\Models\TechPackage::CATEGORIES as $cat)
                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                </x-admin.field>
                <x-admin.field label="Icon" name="icon" placeholder="e.g. JS" hint="Short badge label" wire:model="icon" />
                <x-admin.field label="Title" name="title" placeholder="Package name" icon="edit" required error="{{ $errors->first('title') }}" wire:model="title" />
                <x-admin.field label="Version" name="version" placeholder="e.g. v3.5.0" icon="tag" wire:model="version" />
                <x-admin.field label="Type" name="type" placeholder="e.g. UI Kit" wire:model="type" />
                <x-admin.field label="Command" name="command" placeholder="npm install package" icon="code" wire:model="command" />
                <div class="md:col-span-2">
                    <x-admin.field label="Description" name="description" type="textarea" rows="3" placeholder="What this package does" wire:model="description" />
                </div>
                <x-admin.field label="Selling price" name="price" type="number" step="0.01" min="0" placeholder="e.g. 49" icon="cash" hint="Leave empty for free / info only" error="{{ $errors->first('price') }}" wire:model="price" />
                <x-admin.field label="Billing" name="billing" type="select" icon="refresh" wire:model="billing">
                    @foreach (\App\Models\TechPackage::BILLINGS as $bill)
                        <option value="{{ $bill }}">{{ $bill === 'month' ? 'Per month' : 'One-time' }}</option>
                    @endforeach
                </x-admin.field>
                <x-admin.field label="Validity" name="validity" type="select" icon="clock" hint="Shown on the service list marketplace" wire:model="validity">
                    @foreach (\App\Models\TechPackage::VALIDITIES as $validity)
                        <option value="{{ $validity }}">{{ $validity }}</option>
                    @endforeach
                </x-admin.field>
                <div class="md:col-span-2">
                    <x-admin.field label="Features (one per line)" name="features" type="textarea" rows="3" placeholder="Key features" wire:model="features" />
                </div>
                <div class="md:col-span-2">
                    <x-admin.field label="Install commands (one per line)" name="installCommands" type="textarea" rows="2" placeholder="Steps to install" wire:model="installCommands" />
                </div>
                <x-admin.field label="Repo URL" name="repoUrl" placeholder="https://github.com/..." icon="link" error="{{ $errors->first('repoUrl') }}" wire:model="repoUrl" />
                <x-admin.field label="Docs URL" name="docsUrl" placeholder="https://docs.example.com" icon="doc" error="{{ $errors->first('docsUrl') }}" wire:model="docsUrl" />
                <div>
                    <label for="sort_order" class="label">Sort order</label>
                    <input id="sort_order" type="number" wire:model="sort_order" class="input" />
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="package-form" save-label="{{ $editingId ? 'Update package' : 'Create package' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="card card-pad">
        <h2 class="card-title">Tech Packages ({{ $this->packages->count() }})</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse ($this->packages->take($this->limit) as $package)
                <article class="rounded-2xl border border-white/50 bg-white/40 p-5 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="badge badge-brand">{{ $package->icon ?: strtoupper(substr($package->title, 0, 2)) }}</span>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $package->title }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ ucfirst($package->category) }} @if ($package->version) · {{ $package->version }} @endif
                                    @if ($package->isSellable()) · <span class="font-semibold text-slate-900 dark:text-white">{{ $package->displayPrice() }}</span> @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <select
                                wire:change="setStatus({{ $package->id }}, $event.target.value)"
                                class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $package->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($package->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                                aria-label="Package visibility"
                                name="publish_status"
                            >
                                @foreach (\App\Models\TechPackage::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($package->publish_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <x-admin.actions :view="route('servicelist')" :edit="$package->id" :delete="$package->id" delete-message="Delete this package?" />
                            </div>
                        </div>
                    </div>
                    @if ($package->description)
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">{{ $package->description }}</p>
                    @endif
                    @if ($package->command)
                        <code class="mt-2 block rounded-lg bg-white/50 px-2 py-1 font-mono text-xs text-slate-700 ring-1 ring-inset ring-white/40 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10">{{ $package->command }}</code>
                    @endif
                </article>
            @empty
                <p class="col-span-2 text-sm text-slate-500 dark:text-slate-400">No packages yet.</p>
            @endforelse
        </div>

        @if ($this->hasMore)
            <div class="mt-6 flex flex-col items-center gap-2">
                <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                    <x-icon name="plus" class="icon h-4 w-4" /> Load more packages
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
            </div>
        @endif
    </section>
</div>
