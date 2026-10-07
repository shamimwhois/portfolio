<div>
    <div class="mb-6 flex items-center justify-between">
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New Skill
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit Skill' : 'New Skill' }}" subtitle="Rate your proficiency from 0 to 100" icon="zap" size="md" close-method="cancel">
            <form wire:submit="save" id="skill-form" class="grid gap-4 sm:grid-cols-2">
                <x-admin.field label="Name" name="name" placeholder="Skill name" icon="edit" required error="{{ $errors->first('name') }}" wire:model="name" />
                <x-admin.field label="Icon (short)" name="icon" placeholder="UI / JS / PHP" hint="Short badge label, e.g. PHP" wire:model="icon" />
                <div class="sm:col-span-2">
                    <label for="level" class="label">Level — <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $level }}%</span></label>
                    <input
                        id="level"
                        type="range"
                        min="0"
                        max="100"
                        step="5"
                        wire:model.live="level"
                        class="range-input w-full"
                    />
                    <div class="progress-track mt-3 h-1.5"><div class="progress-fill" style="width: {{ $level }}%"></div></div>
                    @error('level') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="sort_order" class="label">Sort order</label>
                    <input id="sort_order" type="number" wire:model="sort_order" class="input" />
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="skill-form" save-label="{{ $editingId ? 'Update skill' : 'Add skill' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Icon</th>
                    <th>Level</th>
                    <th>Sort</th>
                    <th>Visibility</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->skills->take($this->limit) as $skill)
                    <tr>
                        <td class="font-medium text-slate-900 dark:text-white">{{ $skill->name }}</td>
                        <td><span class="badge badge-neutral">{{ $skill->icon }}</span></td>
                        <td>
                            <div class="flex items-center gap-3 max-w-[180px]">
                                <div class="progress-track flex-1"><div class="progress-fill" style="width: {{ $skill->level }}%"></div></div>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $skill->level }}%</span>
                            </div>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ $skill->sort_order }}</td>
                        <td>
                            <select
                                wire:change="setStatus({{ $skill->id }}, $event.target.value)"
                                class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $skill->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($skill->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                                aria-label="Skill visibility"
                                name="publish_status"
                            >
                                @foreach (\App\Models\Skill::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($skill->publish_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-right">
                            <x-admin.actions :view="route('home').'#skills'" :edit="$skill->id" :delete="$skill->id" delete-message="Delete this skill?" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">No skills found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($this->hasMore)
        <div class="mt-6 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more skills
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
        </div>
    @endif
</div>
