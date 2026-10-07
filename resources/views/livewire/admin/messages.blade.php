<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="card-title mb-0">Inbox</h2>
        <div class="flex gap-2">
            @foreach (['all', 'unread'] as $f)
                <button
                    type="button"
                    wire:click="$set('filter', '{{ $f }}')"
                    class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors {{ $filter === $f ? 'border-brand-400/70 bg-brand-500/15 text-brand-700 dark:border-brand-400/60 dark:bg-brand-400/15 dark:text-brand-300' : 'border-white/50 bg-white/40 text-slate-600 backdrop-blur-md hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
                >
                    {{ ucfirst($f) }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid items-start gap-6 lg:grid-cols-2">
        <section class="card card-pad">
            <h2 class="card-title">Messages ({{ $this->messages->count() }})</h2>
            <div class="mt-4 space-y-3">
                @forelse ($this->messages->take($this->limit) as $message)
                    <button type="button" wire:click="open({{ $message->id }})" class="w-full rounded-2xl border p-4 text-left transition-all duration-200 {{ ! $message->is_read ? 'border-brand-400/50 bg-brand-500/10 backdrop-blur-md dark:border-brand-500/40 dark:bg-brand-400/10' : 'border-white/50 bg-white/40 hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10' }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold {{ $message->is_read ? 'text-slate-700 dark:text-slate-300' : 'text-slate-900 dark:text-white' }}">
                                {{ $message->subject ?: 'Inquiry' }}
                            </p>
                            @if (! $message->is_read)
                                <span class="badge badge-danger text-[10px]">NEW</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $message->name }} · {{ $message->email }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $message->created_at->diffForHumans() }}</p>
                    </button>
            @empty
                <p class="py-4 text-center text-sm text-slate-500 dark:text-slate-400">No messages.</p>
            @endforelse
        </div>

        @if ($this->hasMore)
            <div class="mt-6 flex flex-col items-center gap-2">
                <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                    <x-icon name="plus" class="icon h-4 w-4" /> Load more messages
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
            </div>
        @endif
        </section>

        <section class="card card-pad">
            <h2 class="card-title">Message Detail</h2>
            @php $message = $this->messages->firstWhere('id', $openId); @endphp
            @if ($message)
                <div class="mt-4">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $message->subject ?: 'Inquiry' }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $message->name }} &lt;{{ $message->email }}&gt;</p>
                    @if ($message->phone)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Phone: {{ $message->phone }}</p>
                    @endif
                    <div class="mt-4 rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                        <p class="whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $message->message }}</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="mailto:{{ $message->email }}" class="btn btn-primary btn-sm">Reply by Email</a>
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="markUnread({{ $message->id }})">Mark unread</button>
                        <x-admin.actions :delete="$message->id" delete-message="Delete this message?" />
                    </div>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Select a message to read it.</p>
            @endif
        </section>
    </div>
</div>
