<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative w-full sm:max-w-sm">
            <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" class="input pl-10" placeholder="Search invoice no, name, email..." aria-label="Search orders" />
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach (['all', 'pending', 'paid', 'cancelled', 'refunded'] as $s)
                <button
                    type="button"
                    wire:click="updateStatus('{{ $s }}')"
                    class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors {{ $status === $s ? 'border-brand-400/70 bg-brand-500/15 text-brand-700 dark:border-brand-400/60 dark:bg-brand-400/15 dark:text-brand-300' : 'border-white/50 bg-white/40 text-slate-600 backdrop-blur-md hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
                >
                    {{ ucfirst($s) }} ({{ $this->counts[$s] }})
                </button>
            @endforeach
        </div>
    </div>

    <section class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Item</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->invoices->take($this->limit) as $invoice)
                    <tr>
                        <td class="font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</td>
                        <td>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $invoice->user?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->user?->email ?? '' }}</p>
                            @if ($invoice->notes)
                                <p class="mt-1 max-w-[200px] truncate text-[11px] text-amber-600/90 dark:text-amber-400/90" title="{{ $invoice->notes }}">📝 {{ $invoice->notes }}</p>
                            @endif
                        </td>
                        <td class="text-xs">
                            {{ $invoice->itemLabel() }}
                            @if ($invoice->terms_accepted)
                                <span class="badge badge-neutral ml-1">Terms ✓</span>
                            @endif
                        </td>
                        <td class="font-medium text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format((float) $invoice->amount, 2) }}</td>
                        <td><span class="badge badge-neutral">{{ $invoice->paymentMethodLabel() }}</span></td>
                        <td>
                            <span class="badge {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ $invoice->created_at?->format('M d, Y') ?? '—' }}</td>
                        <td class="whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="view({{ $invoice->id }})" aria-label="View order {{ $invoice->invoice_no }}">
                                    <x-icon name="eye" class="icon h-3.5 w-3.5" /> View
                                </button>
                                @if ($invoice->status === 'pending')
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="markPaid({{ $invoice->id }})" wire:confirm="Mark this order as paid?">Mark paid</button>
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cancel({{ $invoice->id }})" wire:confirm="Cancel this order?">Cancel</button>
                                @elseif ($invoice->status === 'paid')
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="refund({{ $invoice->id }})" wire:confirm="Refund this order?">Refund</button>
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cancel({{ $invoice->id }})" wire:confirm="Cancel this order?">Cancel</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($this->hasMore)
        <div class="mt-6 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more orders
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
        </div>
    @endif

    @if ($this->viewingInvoice)
        @php $inv = $this->viewingInvoice; @endphp
        <x-admin.modal title="Order {{ $inv->invoice_no }}" subtitle="Full order details" icon="receipt" size="md" close-method="closeView">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="badge {{ $inv->status === 'paid' ? 'badge-success' : ($inv->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ ucfirst($inv->status) }}
                    </span>
                    <p class="font-display text-2xl font-bold text-slate-900 dark:text-white">
                        {{ $inv->currency }} {{ number_format((float) $inv->amount, 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-white/60 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Customer</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $inv->user?->name ?? 'Guest' }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $inv->user?->email ?? '—' }}</p>
                    @if ($inv->user?->phone)
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $inv->user->phone }}</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-white/60 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Item</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $inv->itemLabel() }}</p>
                    @if ($inv->tier)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inv->tier->category?->name }} // {{ $inv->tier->billingLabel() }}</p>
                    @elseif ($inv->package)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inv->package->categoryLabel() }} · {{ $inv->package->billingLabel() }}</p>
                    @elseif ($inv->groupPackage)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inv->groupPackage->kindLabel() }} · {{ $inv->groupPackage->billingLabel() }}</p>
                    @endif
                    @php
                        $features = $inv->tier?->features ?? $inv->package?->features ?? $inv->groupPackage?->features ?? [];
                    @endphp
                    @if (! empty($features))
                        <ul class="mt-3 grid gap-1.5 sm:grid-cols-2">
                            @foreach ($features as $feature)
                                <li class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                                    <x-icon name="check" class="icon h-3 w-3 shrink-0 text-emerald-500" /> {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="rounded-2xl border border-white/60 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Payment</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $inv->paymentMethodLabel() }}</p>
                    @if ($inv->gateway)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Via {{ $inv->gateway }} gateway</p>
                    @endif
                    @if ($inv->gateway_reference)
                        <p class="mt-1 font-mono text-xs text-slate-500 dark:text-slate-400">Ref: {{ $inv->gateway_reference }}</p>
                    @endif
                    @if ($inv->paymentMethodAccount())
                        <p class="mt-1 font-mono text-sm text-slate-600 dark:text-slate-300">{{ $inv->paymentMethodAccount() }}</p>
                    @endif
                    @if ($inv->paymentMethodInfo()['instructions'] ?? null)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inv->paymentMethodInfo()['instructions'] }}</p>
                    @endif
                </div>

                <dl class="space-y-2 text-sm">
                    @if ($inv->notes)
                        <div class="flex items-start justify-between gap-4">
                            <dt class="shrink-0 text-slate-500 dark:text-slate-400">Notes</dt>
                            <dd class="text-right text-slate-700 dark:text-slate-300">{{ $inv->notes }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-4">
                        <dt class="shrink-0 text-slate-500 dark:text-slate-400">Placed</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $inv->created_at?->format('M d, Y \a\t H:i') ?? '—' }}</dd>
                    </div>
                    @if ($inv->purchased_at)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="shrink-0 text-slate-500 dark:text-slate-400">Purchased</dt>
                            <dd class="text-slate-700 dark:text-slate-300">{{ $inv->purchased_at->format('M d, Y \a\t H:i') }}</dd>
                        </div>
                    @endif
                    @if ($inv->paid_at)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="shrink-0 text-slate-500 dark:text-slate-400">Paid at</dt>
                            <dd class="text-slate-700 dark:text-slate-300">{{ $inv->paid_at->format('M d, Y \a\t H:i') }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-4">
                        <dt class="shrink-0 text-slate-500 dark:text-slate-400">Terms</dt>
                        <dd>
                            @if ($inv->terms_accepted)
                                <span class="badge badge-success">Accepted ✓</span>
                            @else
                                <span class="badge badge-neutral">Not accepted</span>
                            @endif
                        </dd>
                    </div>
                    @if ($inv->clientProject)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="shrink-0 text-slate-500 dark:text-slate-400">Client project</dt>
                            <dd class="text-slate-700 dark:text-slate-300">{{ $inv->clientProject->title }} ({{ ucfirst($inv->clientProject->status) }})</dd>
                        </div>
                    @endif
                </dl>
            </div>
            <x-slot:footer>
                <div class="flex justify-end">
                    <button type="button" class="btn btn-primary btn-sm" wire:click="closeView">
                        <x-icon name="check" class="icon h-3.5 w-3.5" /> Close
                    </button>
                </div>
            </x-slot:footer>
        </x-admin.modal>
    @endif
</div>
