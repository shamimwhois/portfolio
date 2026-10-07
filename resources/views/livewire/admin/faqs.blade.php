<div>
    <div class="mb-6 flex items-center justify-between">
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New FAQ
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit FAQ' : 'New FAQ' }}" subtitle="Answer common questions from visitors" icon="chat" size="md" close-method="cancel">
            <form wire:submit="save" id="faq-form" class="grid gap-4">
                <x-admin.field label="Question" name="question" placeholder="Frequently asked question" icon="chat" required error="{{ $errors->first('question') }}" wire:model="question" />
                <x-admin.field label="Answer" name="answer" type="textarea" rows="4" placeholder="Detailed answer" required error="{{ $errors->first('answer') }}" wire:model="answer" />
                <div class="sm:max-w-[10rem]">
                    <label for="sort_order" class="label">Sort order</label>
                    <input id="sort_order" type="number" wire:model="sort_order" class="input" />
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="faq-form" save-label="{{ $editingId ? 'Update FAQ' : 'Add FAQ' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="card card-pad">
        <h2 class="card-title">FAQs ({{ $this->matchingTotal }})</h2>
        <div class="mt-4 space-y-3">
            @forelse ($this->faqs->take($this->limit) as $faq)
                <article class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $faq->question }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $faq->answer }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <select
                                wire:change="setStatus({{ $faq->id }}, $event.target.value)"
                                class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $faq->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($faq->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                                aria-label="FAQ visibility"
                                name="publish_status"
                            >
                                @foreach (\App\Models\Faq::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($faq->publish_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <x-admin.actions :view="route('home').'#faq-section'" :edit="$faq->id" :delete="$faq->id" delete-message="Delete this FAQ?" />
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">No FAQs yet.</p>
            @endforelse
        </div>
    </section>

    @if ($this->hasMore)
        <div class="mt-6 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more FAQs
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
        </div>
    @endif
</div>
