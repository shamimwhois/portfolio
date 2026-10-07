@php use App\Models\Setting; @endphp
<div class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3" wire:key="assistant-widget">
    @if ($open)
        <div class="glass-strong w-[min(360px,calc(100vw-2.5rem))] overflow-hidden rounded-2xl shadow-2xl shadow-brand-600/10">
            <div class="flex items-center justify-between border-b border-slate-200/60 px-4 py-3 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-white">
                        <x-icon name="chat" class="icon h-4 w-4" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $assistantName }}</p>
                        <p class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Online · {{ Setting::get('site_name', 'Support') }}
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="resetConversation" class="btn btn-secondary btn-sm" title="Start new chat">New</button>
            </div>

            <div class="border-b border-slate-200/60 px-4 py-2.5 text-xs text-slate-500 dark:border-white/10 dark:text-slate-400">
                {{ $assistantWelcome }}
            </div>

            @if ($this->contactRequired())
                <div class="border-b border-slate-200/60 px-4 py-3 dark:border-white/10">
                    <p class="text-xs font-semibold text-slate-900 dark:text-white">Start the chat</p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                        Tell us how to reach you so we can follow up if you step away.
                    </p>
                    <form wire:submit="submitContact" class="mt-2.5 space-y-2">
                        <div>
                            <input wire:model="guestEmail" type="email" class="input !py-2" placeholder="Email address" autocomplete="email" />
                            @error('guestEmail') <p class="error-text mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input wire:model="guestPhone" type="tel" class="input !py-2" placeholder="Phone number" autocomplete="tel" />
                            @error('guestPhone') <p class="error-text mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-full">Continue</button>
                    </form>
                </div>
            @endif

            <div id="assistantMessages" class="h-72 space-y-2 overflow-y-auto px-4 py-3">
                @forelse ($this->messages as $message)
                    <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $message->id }}">
                        <div class="max-w-[85%] rounded-xl px-3 py-2 text-xs leading-relaxed {{ $message->role === 'user' ? 'bg-gradient-to-br from-brand-600 to-brand-700 text-white shadow-md shadow-brand-600/25' : 'bg-white/60 text-slate-700 ring-1 ring-inset ring-white/50 dark:bg-white/10 dark:text-slate-200 dark:ring-white/10' }}">
                            {{ $message->body }}
                        </div>
                    </div>
                @empty
                    <p class="pt-10 text-center text-xs text-slate-500 dark:text-slate-400">No messages yet. Pick a shortcut or type a question.</p>
                @endforelse
            </div>

            @if (! $this->contactRequired())
                <div class="grid grid-cols-2 gap-2 px-4 py-2">
                    @foreach (['pricing' => 'Pricing', 'delivery' => 'Delivery', 'package' => 'Packages', 'status' => 'Order Status'] as $key => $label)
                        <button type="button" wire:click="sendQuick('{{ $key }}')" class="btn btn-secondary btn-sm">{{ $label }}</button>
                    @endforeach
                </div>

                <form wire:submit="send" class="flex gap-2 px-4 pb-4 pt-1">
                    <input wire:model="question" class="input" placeholder="Ask about pricing, delivery, packages..." />
                    <button type="submit" class="btn btn-primary btn-sm shrink-0" aria-label="Send">
                        <x-icon name="send" class="icon h-4 w-4" />
                    </button>
                </form>
            @endif
        </div>
    @endif

    <button
        type="button"
        wire:click="toggle"
        class="btn btn-primary h-14 rounded-full px-5 shadow-xl shadow-brand-600/35"
        :aria-expanded="{{ $open ? 'true' : 'false' }}"
    >
        <x-icon name="chat" class="icon h-5 w-5" />
        <span class="hidden text-sm sm:inline">{{ $assistantName }}</span>
    </button>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('assistant-received', () => {
                const box = document.getElementById('assistantMessages');
                if (box) box.scrollTop = box.scrollHeight;
            });
        });
    </script>
</div>
