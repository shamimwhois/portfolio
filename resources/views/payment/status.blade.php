@component('components.layouts.hud', ['title' => ucfirst($status).' — Payment'])
<div class="container-shell flex min-h-[60vh] items-center py-14">
    <div class="mx-auto w-full max-w-lg">
        <div class="glass-strong rounded-2xl p-8 text-center sm:p-10">
            @php
                $icon = match ($status) {
                    'success' => 'check-circle',
                    'cancelled' => 'close',
                    default => 'clock',
                };
                $tone = match ($status) {
                    'success' => 'from-emerald-500 to-emerald-600 shadow-emerald-600/30',
                    'cancelled' => 'from-red-500 to-red-600 shadow-red-600/30',
                    default => 'from-amber-500 to-amber-600 shadow-amber-600/30',
                };
            @endphp

            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br text-white shadow-lg ring-1 ring-inset ring-white/25 {{ $tone }}">
                <x-icon name="{{ $icon }}" class="icon h-8 w-8" />
            </span>

            <h1 class="mt-5 font-display text-2xl font-bold text-slate-900 dark:text-white">
                @if ($status === 'success')
                    Payment successful
                @elseif ($status === 'cancelled')
                    Payment cancelled
                @else
                    Payment pending
                @endif
            </h1>

            <p class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                @if ($status === 'success')
                    Your order <span class="font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</span> has been paid
                    and is now being processed. A client project tracker will be available in your panel.
                @elseif ($status === 'cancelled')
                    Your order <span class="font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</span> was not completed.
                    No money has been taken. You can place a new order or try again any time.
                @else
                    Your order <span class="font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</span> is awaiting
                    payment confirmation from the gateway. This usually takes a few seconds — check your panel shortly.
                @endif
            </p>

            <div class="mt-6 divide-y divide-slate-200/60 rounded-2xl border border-white/60 bg-white/40 px-5 text-left backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <div class="flex items-center justify-between gap-3 py-3">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Order</span>
                    <span class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-3">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Item</span>
                    <span class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $invoice->itemLabel() }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-3">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Amount</span>
                    <span class="font-display text-base font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-3">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Method</span>
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $invoice->paymentMethodLabel() }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 py-3">
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</span>
                    <span class="badge {{ $invoice->isPaid() ? 'badge-success' : ($invoice->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>

            <div class="mt-7 flex flex-wrap justify-center gap-3">
                @if (auth()->check())
                    <a href="{{ route('panel.invoices') }}" class="btn btn-primary btn-sm">
                        View in panel
                        <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
                    </a>
                @endif
                <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">Back to home</a>
            </div>
        </div>
    </div>
</div>
@endcomponent
