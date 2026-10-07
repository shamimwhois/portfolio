@php
    $order = $this->lastOrder;
@endphp
<div class="text-center">
    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-600/30 ring-1 ring-inset ring-white/25">
        <x-icon name="check-circle" class="icon h-8 w-8" />
    </span>
    <h4 class="mt-4 font-display text-xl font-semibold text-slate-900 dark:text-white">Order placed!</h4>
    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
        Your {{ $kind }} order has been recorded. Complete the payment below to finalize it — we'll activate it as soon as it's confirmed.
    </p>

    <div class="mt-6 divide-y divide-slate-200/60 rounded-2xl border border-white/60 bg-white/40 px-5 text-left backdrop-blur-md dark:border-white/10 dark:bg-white/5">
        <div class="flex items-center justify-between gap-3 py-3">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Invoice</span>
            <span class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $order->invoice_no }}</span>
        </div>
        <div class="flex items-center justify-between gap-3 py-3">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Item</span>
            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $order->itemLabel() }}</span>
        </div>
        <div class="flex items-center justify-between gap-3 py-3">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Amount</span>
            <span class="font-display text-base font-bold text-slate-900 dark:text-white">{{ $order->currency }} {{ number_format($order->amount, 2) }}</span>
        </div>
        <div class="flex items-center justify-between gap-3 py-3">
            <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</span>
            <span class="badge badge-warning">{{ ucfirst($order->status) }}</span>
        </div>
        @if ($order->payment_method)
            <div class="flex items-center justify-between gap-3 py-3">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Method</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $order->paymentMethodLabel() }}</span>
            </div>
            <div class="py-3">
                <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Pay to</span>
                <p class="mt-1 font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $order->paymentMethodAccount() ?? '—' }}</p>
                @if ($order->paymentMethodInfo()['instructions'] ?? null)
                    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $order->paymentMethodInfo()['instructions'] }}</p>
                @endif
            </div>
        @endif
    </div>

    <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="{{ route('panel.invoices') }}" class="btn btn-primary btn-sm">
            View in panel
            <x-icon name="arrow-right" class="icon h-3.5 w-3.5" />
        </a>
        <button type="button" @click="close()" class="btn btn-secondary btn-sm">Keep browsing</button>
    </div>
</div>
