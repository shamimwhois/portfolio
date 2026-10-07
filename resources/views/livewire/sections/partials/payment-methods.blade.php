@php
    $methods = \App\Support\Payments::enabledMethods();
    $selected = \App\Support\Payments::method($paymentMethod);
    $hasSelection = (bool) ($paymentMethod && $selected);
@endphp
<div class="mt-5">
    <p class="text-sm font-semibold text-slate-900 dark:text-white">Select payment method</p>
    <div class="mt-3 grid grid-cols-1 gap-3 min-[420px]:grid-cols-2">
        @foreach ($methods as $key => $method)
            @php
                $isGateway = (bool) ($method['gateway'] ?? false);
                $isSelected = $paymentMethod === $key;
            @endphp
            <button
                type="button"
                wire:click="setPaymentMethod('{{ $key }}')"
                class="group flex flex-col rounded-2xl border p-4 text-left backdrop-blur-xl transition-all duration-200 {{ $isSelected ? 'border-brand-400/70 bg-brand-500/15 shadow-md shadow-brand-600/10 dark:border-brand-400/60 dark:bg-brand-400/15' : 'border-white/50 bg-white/40 hover:-translate-y-px hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
                aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                aria-label="Pay with {{ $method['label'] }}"
            >
                {{-- Card header --}}
                <div class="flex items-start justify-between gap-2">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-xs font-bold text-white shadow-md shadow-brand-600/25">
                        {{ strtoupper(substr($method['label'] ?? $key, 0, 2)) }}
                    </span>
                    <span class="{{ $isSelected ? 'badge badge-success' : 'badge badge-neutral' }} uppercase">
                        {{ $isSelected ? 'Selected' : 'Select' }}
                    </span>
                </div>

                {{-- Label + type --}}
                <p class="mt-3 truncate font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $method['label'] }}</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $method['type'] }}</p>

                {{-- Summary --}}
                <div class="mt-3 space-y-1.5 text-xs">
                    @if ($isGateway)
                        <p class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <x-icon name="lock" class="icon h-3.5 w-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                            Secure gateway
                        </p>
                    @else
                        <p class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <x-icon name="receipt" class="icon h-3.5 w-3.5 shrink-0 text-brand-500" />
                            <span class="truncate">{{ $method['account'] ?: 'Manual transfer' }}</span>
                        </p>
                    @endif
                </div>
            </button>
        @endforeach
    </div>

    {{-- Selected method details --}}
    <div
        x-show="{{ $hasSelection ? 'true' : 'false' }}"
        x-collapse
        x-cloak
        class="mt-4"
    >
        @if ($hasSelection)
            @php $isGateway = (bool) ($selected['gateway'] ?? false); @endphp
            <div class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-xs font-bold text-white shadow-md shadow-brand-600/25">
                            {{ strtoupper(substr($selected['label'] ?? $paymentMethod, 0, 2)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $selected['label'] }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                {{ $isGateway ? 'Secure gateway payment' : 'Payment instructions' }}
                            </p>
                        </div>
                    </div>
                    <span class="badge badge-success shrink-0">Selected</span>
                </div>

                @if ($isGateway)
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $selected['instructions'] }}</p>
                    <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <x-icon name="lock" class="icon h-3.5 w-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                        You'll be redirected to {{ $selected['label'] }} to complete the payment securely.
                    </p>
                @else
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $selected['instructions'] }}</p>
                    <p class="mt-2 text-sm text-slate-900 dark:text-white">
                        Account: <span class="font-mono font-semibold">{{ $selected['account'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">({{ $selected['type'] }})</span>
                    </p>
                @endif
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button
                    type="button"
                    wire:click="confirmPurchase"
                    wire:loading.attr="disabled"
                    class="btn btn-primary btn-sm"
                >
                    <span wire:loading.remove wire:target="confirmPurchase">
                        <x-icon name="lock" class="icon h-3.5 w-3.5" />
                        {{ $isGateway ? 'Proceed to '.$selected['label'] : 'Confirm payment' }}
                    </span>
                    <span wire:loading wire:target="confirmPurchase">
                        <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        {{ $isGateway ? 'Contacting gateway...' : 'Placing order...' }}
                    </span>
                </button>
                <button type="button" wire:click="cancelCheckout" class="btn btn-secondary btn-sm">Cancel</button>
            </div>
        @endif
    </div>
</div>
