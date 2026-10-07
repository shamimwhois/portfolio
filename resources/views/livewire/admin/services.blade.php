<div>
    <div class="mb-6 flex items-center justify-between">
        <button type="button" class="btn btn-primary btn-sm" wire:click="createCategory">
            <x-icon name="plus" class="icon h-4 w-4" /> New Category
        </button>
    </div>

    @if ($showCategoryForm)
        <x-admin.modal title="{{ $editingCategoryId ? 'Edit Category' : 'New Category' }}" subtitle="Group related service plans together" icon="folder" size="sm" close-method="cancelCategory">
            <form wire:submit="saveCategory" id="category-form" class="grid gap-4">
                <x-admin.field label="Name" name="categoryName" placeholder="Category name" icon="edit" required error="{{ $errors->first('categoryName') }}" wire:model="categoryName" />
                <x-admin.field label="Slug" name="categorySlug" placeholder="auto-generated" hint="Used in the URL — leave blank to auto-generate" required error="{{ $errors->first('categorySlug') }}" wire:model="categorySlug" />
                <div>
                    <label for="categorySort" class="label">Sort order</label>
                    <input id="categorySort" type="number" wire:model="categorySort" class="input" />
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="category-form" save-method="saveCategory" cancel-method="cancelCategory" save-label="{{ $editingCategoryId ? 'Update category' : 'Create category' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    @if ($showTierForm)
        <x-admin.modal title="{{ $editingTierId ? 'Edit Plan' : 'New Plan' }}" subtitle="Define pricing, billing and features" icon="box" size="lg" close-method="cancelTier">
            <form wire:submit="saveTier" id="tier-form" class="grid gap-4 md:grid-cols-2">
                <x-admin.field label="Category" name="tierCategoryId" type="select" icon="folder" required error="{{ $errors->first('tierCategoryId') }}" wire:model="tierCategoryId">
                    @foreach ($this->allCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-admin.field>
                <x-admin.field label="Plan Name" name="tierName" placeholder="e.g. Startup" icon="edit" required error="{{ $errors->first('tierName') }}" wire:model="tierName" />
                <x-admin.field label="Price" name="tierPrice" placeholder="e.g. $1,499" icon="cash" required error="{{ $errors->first('tierPrice') }}" wire:model="tierPrice" />
                <x-admin.field label="Billing" name="tierBilling" type="select" icon="refresh" wire:model="tierBilling">
                    <option value="one-time">One-time</option>
                    <option value="month">Per month</option>
                </x-admin.field>
                <div class="md:col-span-2">
                    <x-admin.field label="Features (one per line)" name="tierFeatures" type="textarea" rows="4" placeholder="Feature list" hint="Each line becomes a checkmark on the pricing card" wire:model="tierFeatures" />
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 pb-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <input type="checkbox" wire:model="tierPopular" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-white/10" /> Popular badge
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.field label="CTA style" name="tierCta" type="select" icon="zap" wire:model="tierCta">
                        <option value="outline">Outline</option>
                        <option value="gradient">Gradient</option>
                    </x-admin.field>
                    <div>
                        <label for="tierSort" class="label">Sort order</label>
                        <input id="tierSort" type="number" wire:model="tierSort" class="input" />
                    </div>
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="tier-form" save-method="saveTier" cancel-method="cancelTier" save-label="{{ $editingTierId ? 'Update plan' : 'Create plan' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <div class="space-y-6">
        @forelse ($this->categories->take($this->limit) as $category)
            <section class="card card-pad">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="card-title">{{ $category->name }} <span class="text-sm font-normal text-slate-500 dark:text-slate-400">({{ $category->tiers->count() }} plans)</span></h2>
                    <div class="flex flex-wrap items-center gap-2">
                        <select
                            wire:change="setCategoryStatus({{ $category->id }}, $event.target.value)"
                            class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $category->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($category->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                            aria-label="Category visibility"
                            name="publish_status"
                        >
                            @foreach (\App\Models\ServiceCategory::STATUSES as $status)
                                <option value="{{ $status }}" @selected($category->publish_status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="createTier({{ $category->id }})">
                            <x-icon name="plus" class="icon h-3.5 w-3.5" /> Plan
                        </button>
                        <x-admin.actions :view="route('home').'#services'" :edit="$category->id" :delete="$category->id" edit-method="editCategory" delete-method="deleteCategory" delete-message="Delete this category and its plans?" />
                    </div>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @forelse ($category->tiers as $tier)
                        <article class="rounded-2xl border bg-white/40 p-5 backdrop-blur-md {{ $tier->popular ? 'border-brand-300/80 ring-1 ring-brand-300/60 dark:border-brand-500/50 dark:ring-brand-500/30' : 'border-white/50 dark:border-white/10' }}">
                            <p class="font-display text-2xl font-bold text-slate-900 dark:text-white">{{ $tier->price }}</p>
                            <p class="mt-1 text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $tier->name }}</p>
                            <ul class="mb-4 mt-3 space-y-1.5 text-sm text-slate-600 dark:text-slate-400">
                                @foreach ($tier->features as $feature)
                                    <li class="flex items-start gap-2">
                                        <x-icon name="check" class="icon mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-600 dark:text-brand-400" />
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="flex flex-wrap items-center gap-2">
                                <select
                                    wire:change="setTierStatus({{ $tier->id }}, $event.target.value)"
                                    class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $tier->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($tier->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                                    aria-label="Plan visibility"
                                    name="publish_status"
                                >
                                    @foreach (\App\Models\ServiceTier::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($tier->publish_status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <x-admin.actions :view="route('home').'#services'" :edit="$tier->id" :delete="$tier->id" edit-method="editTier" delete-method="deleteTier" delete-message="Delete this plan?" />
                            </div>
                        </article>
                    @empty
                        <p class="col-span-3 text-sm text-slate-500 dark:text-slate-400">No plans in this category yet.</p>
                    @endforelse
                </div>
            </section>
        @empty
            <section class="card card-pad">
                <p class="text-sm text-slate-500 dark:text-slate-400">No service categories yet.</p>
            </section>
        @endforelse
    </div>

    @if ($this->hasMore)
        <div class="mt-8 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more categories
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }} categories</p>
        </div>
    @endif
</div>
