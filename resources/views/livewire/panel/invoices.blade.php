<div>
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach (['all' => 'All', 'pending' => 'Pending', 'paid' => 'Paid', 'cancelled' => 'Cancelled'] as $key => $label)
            <button
                type="button"
                wire:click="$set('status', '{{ $key === 'all' ? '' : $key }}')"
                class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all duration-200 {{ $status === ($key === 'all' ? '' : $key) ? 'border-brand-400/70 bg-brand-500/15 text-brand-700 shadow-md shadow-brand-600/10 dark:border-brand-400/60 dark:bg-brand-400/15 dark:text-brand-300' : 'border-white/50 bg-white/40 text-slate-600 backdrop-blur-md hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
            >
                {{ $label }} ({{ $this->counts[$key] }})
            </button>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($this->invoices as $invoice)
            <article class="card card-pad spotlight">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-700 text-white shadow-md shadow-brand-600/25 ring-1 ring-inset ring-white/25">
                            <x-icon name="{{ $invoice->status === 'paid' ? 'check-circle' : ($invoice->status === 'pending' ? 'clock' : 'close') }}" class="icon h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</p>
                            <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">{{ $invoice->itemLabel() }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $invoice->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <p class="font-display text-xl font-bold text-slate-900 dark:text-white">
                            {{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}
                        </p>
                        <span class="badge {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'pending' ? 'badge-warning' : 'badge-neutral') }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                </div>

                @if ($invoice->payment_method)
                    <div class="mt-4 flex items-center gap-2 border-t border-slate-200/60 pt-3 dark:border-white/10">
                        <span class="badge badge-neutral">{{ $invoice->paymentMethodLabel() }}</span>
                        @if ($invoice->status === 'pending')
                            <span class="text-xs text-slate-500 dark:text-slate-400">Awaiting payment</span>
                        @endif
                    </div>
                @endif

                @if ($invoice->status === 'pending')
                    @php $isGateway = \App\Support\Payments::isGateway((string) $invoice->payment_method); @endphp
                    @if ($isGateway)
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200/70 bg-brand-50/60 px-4 py-3 backdrop-blur-md dark:border-brand-400/20 dark:bg-brand-400/10">
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                Complete your payment with <span class="font-semibold text-slate-900 dark:text-white">{{ $invoice->paymentMethodLabel() }}</span> — you'll be redirected to the secure checkout page.
                            </p>
                            <button type="button" wire:click="payNow({{ $invoice->id }})" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                <x-icon name="external" class="icon h-3.5 w-3.5" /> Pay now
                            </button>
                        </div>
                    @elseif ($invoice->paymentMethodAccount())
                        <div class="mt-3 rounded-xl border border-amber-200/70 bg-amber-50/60 px-4 py-3 backdrop-blur-md dark:border-amber-400/20 dark:bg-amber-400/10">
                            <p class="text-xs font-medium uppercase tracking-wider text-amber-700 dark:text-amber-300">How to pay</p>
                            <p class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">
                                Send <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</span>
                                to <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $invoice->paymentMethodAccount() }}</span>
                                via {{ $invoice->paymentMethodLabel() }}.
                            </p>
                            @if ($invoice->paymentMethodInfo()['instructions'] ?? null)
                                <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $invoice->paymentMethodInfo()['instructions'] }}</p>
                            @endif
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Mention your invoice number in the payment note so it can be matched.</p>
                        </div>
                    @endif
                @endif
            </article>
        @empty
            <div class="card card-pad py-10 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-500/10 text-slate-400">
                    <x-icon name="receipt" class="icon h-6 w-6" />
                </span>
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No invoices match this filter.</p>
                <a href="{{ route('home') }}#services" class="btn btn-primary btn-sm mt-4">Browse services</a>
            </div>
        @endforelse
    </div>
</div>
