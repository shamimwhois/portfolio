<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="card-title mb-0">Support Conversations</h2>
        <div class="flex gap-2">
            @foreach (['open', 'closed', 'all'] as $s)
                <button
                    type="button"
                    wire:click="$set('filter', '{{ $s }}')"
                    class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors {{ $filter === $s ? 'border-brand-400/70 bg-brand-500/15 text-brand-700 dark:border-brand-400/60 dark:bg-brand-400/15 dark:text-brand-300' : 'border-white/50 bg-white/40 text-slate-600 backdrop-blur-md hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
                >
                    {{ ucfirst($s) }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-[320px_1fr]">
        <section class="card card-pad !p-3">
            <h2 class="card-title !text-sm">Threads ({{ $this->conversations->count() }})</h2>
            <div class="mt-3 max-h-[30rem] space-y-2 overflow-y-auto pr-1">
                @forelse ($this->conversations->take($this->limit) as $conversation)
                    <button
                        type="button"
                        wire:key="conv-{{ $conversation->id }}"
                        wire:click="select({{ $conversation->id }})"
                        class="w-full rounded-xl border px-3 py-2 text-left text-xs transition-all duration-200 {{ $conversationId === $conversation->id ? 'border-brand-400/60 bg-brand-500/15 text-slate-900 backdrop-blur-md dark:border-brand-500/50 dark:bg-brand-400/15 dark:text-white' : 'border-white/50 bg-white/40 text-slate-700 hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
                    >
                        <p class="flex items-center justify-between gap-2">
                            <span class="truncate font-semibold">{{ $conversation->user?->name ?? 'Guest' }}</span>
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $conversation->isOpen() ? 'bg-brand-600 dark:bg-brand-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        </p>
                        <p class="mt-1 truncate text-[10px] text-slate-500 dark:text-slate-400">
                            @if ($conversation->guest_email)
                                {{ $conversation->guest_email }} · {{ $conversation->guest_phone }}
                            @else
                                {{ $conversation->source }} · {{ $conversation->messages_count }} msgs
                            @endif
                        </p>
                        <p class="mt-1 truncate text-slate-500 dark:text-slate-400">
                            {{ $conversation->messages->first()?->body ?? 'No messages' }}
                        </p>
                    </button>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">No conversations.</p>
                @endforelse
            </div>

            @if ($this->hasMore)
                <div class="mt-4 flex flex-col items-center gap-1.5">
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm w-full justify-center">
                        <x-icon name="plus" class="icon h-3.5 w-3.5" /> Load more
                    </button>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
                </div>
            @endif
        </section>

        <section class="card flex h-[34rem] flex-col overflow-hidden">
            @if ($this->selectedConversation)
                @php $selected = $this->selectedConversation; @endphp
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $selected->user?->name ?? 'Guest' }}
                            @if ($selected->user)
                                <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">· {{ $selected->user->email }}</span>
                            @elseif ($selected->guest_email)
                                <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">· {{ $selected->guest_email }}</span>
                            @endif
                        </p>
                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                            @if ($selected->guest_phone)
                                <span class="inline-flex items-center gap-1">📞 {{ $selected->guest_phone }}</span>
                            @endif
                            {{ $selected->isOpen() ? 'OPEN' : 'CLOSED' }} · {{ $selected->source }} · #{{ $selected->id }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @if ($selected->isOpen())
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="close({{ $selected->id }})">Close</button>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="reopen({{ $selected->id }})">Reopen</button>
                        @endif
                    </div>
                </div>

                <div id="adminChatMessages" class="flex-1 space-y-2 overflow-y-auto px-4 py-3">
                    @forelse ($this->messages as $message)
                        <div class="flex {{ $message->role === 'user' ? 'justify-start' : 'justify-end' }}" wire:key="msg-{{ $message->id }}">
                            <div class="max-w-[80%] rounded-2xl px-3 py-2 text-sm leading-relaxed {{ $message->role === 'user' ? 'border border-white/50 bg-white/40 text-slate-700 backdrop-blur-md dark:border-white/10 dark:bg-white/5 dark:text-slate-300' : 'bg-gradient-to-br from-brand-600 to-brand-700 text-white shadow-md shadow-brand-600/25' }}">
                                {{ $message->body }}
                                <p class="mt-1 text-[10px] opacity-60">{{ $message->created_at->format('M d H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="pt-16 text-center text-sm text-slate-500 dark:text-slate-400">No messages in this thread.</p>
                    @endforelse
                </div>

                <form wire:submit="sendReply" class="flex gap-2 border-t border-slate-100 px-4 py-3 dark:border-slate-800">
                    <input wire:model="reply" class="input flex-1" placeholder="Reply as support team..." />
                    <button type="submit" class="btn btn-primary btn-sm">Send</button>
                </form>
            @else
                <div class="flex flex-1 items-center justify-center">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Select a conversation to view the thread.</p>
                </div>
            @endif
        </section>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('conversation-selected', () => {
                const box = document.getElementById('adminChatMessages');
                if (box) box.scrollTop = box.scrollHeight;
            });
        });
    </script>
</div>
