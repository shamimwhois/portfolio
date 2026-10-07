<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Combo bundles and single shared tool licenses sold on the Group Buy marketplace.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New Item
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit Group Item' : 'New Group Item' }}" subtitle="Combo bundles &amp; single tools for the group buy page" icon="box" size="lg" close-method="cancel">
            <form wire:submit="save" id="group-package-form" class="grid gap-4 md:grid-cols-2">
                <x-admin.field label="Type" name="kind" type="select" icon="layers" wire:model="kind">
                    <option value="combo">Combo Package</option>
                    <option value="tool">Single Tool</option>
                </x-admin.field>
                <x-admin.field label="Title" name="title" placeholder="e.g. SEO Package" icon="edit" required error="{{ $errors->first('title') }}" wire:model="title" />
                <x-admin.field label="Icon" name="icon" placeholder="e.g. AH" hint="Short badge label" wire:model="icon" />
                <x-admin.field label="Selling price" name="price" type="number" step="0.01" min="0" placeholder="e.g. 299 (BDT)" icon="cash" hint="Priced in ৳ on the marketplace" error="{{ $errors->first('price') }}" wire:model="price" />
                <x-admin.field label="Billing" name="billing" type="select" icon="refresh" wire:model="billing">
                    @foreach (\App\Models\GroupPackage::BILLINGS as $bill)
                        <option value="{{ $bill }}">{{ $bill === 'month' ? 'Per month' : 'One-time' }}</option>
                    @endforeach
                </x-admin.field>
                <div>
                    <label for="sort_order" class="label">Sort order</label>
                    <input id="sort_order" type="number" wire:model="sort_order" class="input" />
                </div>
                <div class="md:col-span-2">
                    <x-admin.field label="Description" name="description" type="textarea" rows="2" placeholder="Short description (optional)" wire:model="description" />
                </div>
                <div class="md:col-span-2">
                    <x-admin.field label="Features (one per line)" name="features" type="textarea" rows="4" placeholder="Included tools / features" wire:model="features" />
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="group-package-form" save-label="{{ $editingId ? 'Update item' : 'Create item' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="card card-pad">
        <h2 class="card-title">Combo Packages ({{ $this->combos->count() }})</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse ($this->combos->take($this->limit) as $package)
                @include('livewire.admin.partials.group-package-card')
            @empty
                <p class="col-span-2 text-sm text-slate-500 dark:text-slate-400">Nothing here yet.</p>
            @endforelse
        </div>
    </section>

    <section class="card card-pad mt-6">
        <h2 class="card-title">Single Tools ({{ $this->tools->count() }})</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse ($this->tools->take($this->limit) as $package)
                @include('livewire.admin.partials.group-package-card')
            @empty
                <p class="col-span-2 text-sm text-slate-500 dark:text-slate-400">Nothing here yet.</p>
            @endforelse
        </div>
    </section>

    @if ($this->hasMore)
        <div class="mt-6 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more items
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
        </div>
    @endif
</div>
