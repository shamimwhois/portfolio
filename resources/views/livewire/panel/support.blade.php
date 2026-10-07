<div>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="card-title mb-0">Support Channel</h2>
        <button type="button" wire:click="newConversation" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="icon h-4 w-4" /> New Chat
        </button>
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-[260px_1fr]">
        <div class="card card-pad !p-3">
            <p class="mb-2 px-2 text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Conversations</p>
            <div class="max-h-[28rem] space-y-2 overflow-y-auto pr-1">
                @forelse ($this->conversations as $conversation)
                    <button
                        type="button"
                        wire:key="conv-{{ $conversation->id }}"
                        wire:click="openConversation({{ $conversation->id }})"
                        class="w-full rounded-xl border px-3 py-2 text-left text-xs transition-all duration-200 {{ $conversationId === $conversation->id ? 'border-brand-400/60 bg-brand-500/15 text-slate-900 backdrop-blur-md dark:border-brand-500/50 dark:bg-brand-400/15 dark:text-white' : 'border-white/50 bg-white/40 text-slate-700 hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
                    >
                        <p class="flex items-center justify-between gap-2">
                            <span class="truncate font-semibold">{{ $conversation->title }}</span>
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $conversation->isOpen() ? 'bg-brand-600 dark:bg-brand-400' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        </p>
                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            {{ $conversation->messages_count }} msgs · {{ $conversation->last_message_at?->format('M d H:i') ?? $conversation->created_at->format('M d H:i') }}
                        </p>
                    </button>
                @empty
                    <p class="py-4 text-center text-sm text-slate-500 dark:text-slate-400">No conversations yet. Start a new chat.</p>
                @endforelse
            </div>
        </div>

        <div class="card flex h-[32rem] flex-col overflow-hidden">
            @if ($this->selectedConversation)
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $this->selectedConversation->title }}</p>
                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $this->selectedConversation->isOpen() ? 'OPEN' : 'CLOSED' }} · chat #{{ $this->selectedConversation->id }}
                        </p>
                    </div>
                    @if ($this->selectedConversation->isOpen())
                        <button type="button" wire:click="closeConversation" class="btn btn-secondary btn-sm">Close</button>
                    @endif
                </div>

                <div id="panelChatMessages" class="flex-1 space-y-2 overflow-y-auto px-4 py-3">
                    @forelse ($this->messages as $message)
                        <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $message->id }}">
                            <div class="max-w-[80%] rounded-2xl px-3 py-2 text-sm leading-relaxed {{ $message->role === 'user' ? 'bg-gradient-to-br from-brand-600 to-brand-700 text-white shadow-md shadow-brand-600/25' : 'border border-white/50 bg-white/40 text-slate-700 backdrop-blur-md dark:border-white/10 dark:bg-white/5 dark:text-slate-300' }}">
                                {{ $message->body }}
                                <p class="mt-1 text-[10px] opacity-60">{{ $message->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="pt-16 text-center text-sm text-slate-500 dark:text-slate-400">Send a message to reach support.</p>
                    @endforelse
                </div>

                @if ($this->selectedConversation->isOpen())
                    <form wire:submit="send" class="flex gap-2 border-t border-slate-100 px-4 py-3 dark:border-slate-800">
                        <input wire:model="message" class="input flex-1" placeholder="Type a message..." />
                        <button type="submit" class="btn btn-primary btn-sm">Send</button>
                    </form>
                @else
                    <p class="border-t border-slate-100 px-4 py-3 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">This conversation is closed. Start a new chat to continue.</p>
                @endif
            @else
                <div class="flex flex-1 items-center justify-center">
                    <div class="px-6 text-center">
                        <p class="font-semibold text-slate-900 dark:text-white">No active chat</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Select a conversation or start a new one.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('panel-chat-received', () => {
                const box = document.getElementById('panelChatMessages');
                if (box) box.scrollTop = box.scrollHeight;
            });
        });
    </script>
</div>
