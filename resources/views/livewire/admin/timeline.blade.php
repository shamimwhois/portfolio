<div>
    <div class="mb-6 flex items-center justify-between">
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New Phase
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit Phase' : 'New Phase' }}" subtitle="Map out the delivery process step by step" icon="clock" size="md" close-method="cancel">
            <form wire:submit="save" id="phase-form" class="grid gap-4 md:grid-cols-2">
                <x-admin.field label="Duration" name="days" placeholder="e.g. Days 01–04" icon="clock" required error="{{ $errors->first('days') }}" wire:model="days" />
                <x-admin.field label="Title" name="title" placeholder="Phase title" icon="edit" required error="{{ $errors->first('title') }}" wire:model="title" />
                <div class="md:col-span-2">
                    <x-admin.field label="Description" name="description" type="textarea" rows="3" placeholder="What happens in this phase" required error="{{ $errors->first('description') }}" wire:model="description" />
                </div>
                <div class="sm:max-w-[10rem]">
                    <label for="sort_order" class="label">Sort order</label>
                    <input id="sort_order" type="number" wire:model="sort_order" class="input" />
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="phase-form" save-label="{{ $editingId ? 'Update phase' : 'Add phase' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="card card-pad">
        <h2 class="card-title">Timeline Phases ({{ $this->matchingTotal }})</h2>
        <div class="mt-4 space-y-3">
            @forelse ($this->phases->take($this->limit) as $phase)
                <article class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            <span class="badge badge-brand shrink-0">{{ $phase->days }}</span>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $phase->title }}</h3>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $phase->description }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <select
                                wire:change="setStatus({{ $phase->id }}, $event.target.value)"
                                class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $phase->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($phase->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                                aria-label="Phase visibility"
                                name="publish_status"
                            >
                                @foreach (\App\Models\TimelinePhase::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($phase->publish_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <x-admin.actions :view="route('home').'#timeline'" :edit="$phase->id" :delete="$phase->id" delete-message="Delete this phase?" />
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">No phases yet.</p>
            @endforelse
        </div>
    </section>

    @if ($this->hasMore)
        <div class="mt-6 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more phases
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
        </div>
    @endif
</div>
