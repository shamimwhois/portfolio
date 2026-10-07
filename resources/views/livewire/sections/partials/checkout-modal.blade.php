@php
    $done = (bool) $this->lastOrder;
    $step = $done ? 3 : ($reviewed ? 2 : 1);
@endphp

@if ($done || $item)
    <div
        class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="checkout-modal-title"
        x-data="checkoutLock"
        @keydown.escape.window="close()"
    >
        <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-md" wire:click="closeCheckout" aria-hidden="true"></div>

        <div class="glass-strong animate-modal-in relative w-full overflow-hidden rounded-t-2xl sm:max-w-lg sm:rounded-2xl">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200/60 px-5 py-4 dark:border-white/10">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Secure checkout</p>
                    <h3 id="checkout-modal-title" class="truncate font-display text-lg font-semibold text-slate-900 dark:text-white">
                        {{ $done ? 'Order confirmed' : ($step === 1 ? 'Review your order' : 'Complete your order') }}
                    </h3>
                </div>
                <button type="button" class="icon-btn shrink-0" @click="close()" aria-label="Close checkout">
                    <x-icon name="close" class="icon h-5 w-5" />
                </button>
            </div>

            <div class="flex items-center gap-2 px-5 pt-4 sm:gap-3">
                @foreach (['Review', 'Payment', 'Done'] as $i => $label)
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold transition-colors duration-200 {{ $step > $i + 1 ? 'bg-gradient-to-br from-brand-600 to-brand-700 text-white' : ($step === $i + 1 ? 'bg-brand-500/15 text-brand-700 ring-2 ring-brand-500 dark:text-brand-300' : 'bg-slate-500/10 text-slate-500 dark:text-slate-400') }}"
                        >
                            @if ($step > $i + 1)
                                <x-icon name="check" class="icon h-3 w-3" />
                            @else
                                {{ $i + 1 }}
                            @endif
                        </span>
                        <span class="text-xs font-medium {{ $step >= $i + 1 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">{{ $label }}</span>
                        @if ($i < 2)
                            <span class="hidden h-px w-6 bg-slate-200 sm:block dark:bg-white/10"></span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="max-h-[68vh] overflow-y-auto px-5 pb-6 pt-4">
                @if ($done)
                    @include('livewire.sections.partials.order-confirmed', ['kind' => $kind])
                @elseif ($step === 1)
                    @include('livewire.sections.partials.checkout-summary', ['item' => $item, 'kind' => $kind])

                    <div class="mt-5">
                        <label for="checkout-notes" class="text-sm font-semibold text-slate-900 dark:text-white">Order notes</label>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Optional — anything we should know before we start.</p>
                        <textarea
                            id="checkout-notes"
                            wire:model="notes"
                            rows="2"
                            class="input mt-2 resize-none"
                            placeholder="e.g. I need mobile-first priority, launch by next month..."
                            maxlength="1000"
                        ></textarea>
                        @error('notes') <p class="error-text mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4 rounded-xl border border-white/50 bg-white/40 px-4 py-3 text-sm backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                        <p class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <x-icon name="clock" class="icon h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                            Estimated delivery: <span class="font-semibold text-slate-900 dark:text-white">work starts within 48h of payment</span>
                        </p>
                        <p class="mt-1 pl-6 text-xs text-slate-500 dark:text-slate-400">You'll get a live project tracker in your panel once the order is confirmed.</p>
                    </div>

                    <label class="mt-4 flex cursor-pointer items-start gap-2.5 text-sm text-slate-700 dark:text-slate-300">
                        <input
                            type="checkbox"
                            wire:model="termsAccepted"
                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-white/10"
                        />
                        <span>
                            I agree to the <a href="{{ route('home') }}#faq-section" target="_blank" class="font-semibold text-brand-600 underline-offset-2 hover:underline dark:text-brand-400">terms &amp; policies</a> — including the project agreement, cancellation and return policy.
                        </span>
                    </label>
                    @error('termsAccepted') <p class="error-text mt-1">{{ $message }}</p> @enderror

                    <div class="mt-5 flex flex-wrap gap-3">
                        <button type="button" wire:click="goToPayment" class="btn btn-primary btn-sm">
                            Continue to payment
                            <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
                        </button>
                        <button type="button" wire:click="cancelCheckout" class="btn btn-secondary btn-sm">Cancel</button>
                    </div>
                @else
                    @include('livewire.sections.partials.payment-methods')

                    <div class="mt-3">
                        <button type="button" wire:click="backToReview" class="text-xs font-medium text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                            ← Back to review
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
