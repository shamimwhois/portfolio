<div>
    @if ($this->activeServices->isEmpty())
        <div class="card card-pad">
            <h2 class="card-title">No Active Services</h2>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                You have no paid services or licenses yet. Browse the tech stack or service center and select something to get started.
            </p>
            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('home') }}#services" class="btn btn-primary btn-sm">Browse Services</a>
                <a href="{{ route('home') }}#stack" class="btn btn-secondary btn-sm">Browse Packages</a>
            </div>
        </div>
    @else
        <div class="grid items-start gap-6 lg:grid-cols-2">
            @foreach ($this->activeServices as $invoice)
                <div class="card card-pad">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-display text-base font-semibold text-slate-900 dark:text-white">{{ $invoice->package?->title ?? $invoice->tier?->category?->name ?? 'Service' }}</p>
                            <p class="mt-1 font-display text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($invoice->amount, 2) }}</p>
                        </div>
                        <span class="badge badge-success shrink-0">{{ ucfirst($invoice->status) }}</span>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="badge badge-brand">{{ $invoice->package?->categoryLabel() ?? $invoice->tier?->name ?? 'Custom' }}</span>
                        @if ($invoice->package?->isSellable())
                            <span class="badge badge-neutral">{{ $invoice->package->billingLabel() }}</span>
                        @endif
                        @if ($invoice->payment_method)
                            <span class="badge badge-neutral">{{ $invoice->paymentMethodLabel() }}</span>
                        @endif
                    </div>
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @forelse (($invoice->package?->features ?? $invoice->tier?->features ?? []) as $feature)
                            <li class="badge badge-neutral">{{ $feature }}</li>
                        @empty
                            <li class="badge badge-neutral">License access</li>
                        @endforelse
                    </ul>
                    @if ($invoice->package)
                        <div class="mt-4">
                            <code class="block rounded-lg bg-white/50 px-2 py-1 font-mono text-xs text-slate-700 ring-1 ring-inset ring-white/40 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10">{{ $invoice->package->command }}</code>
                        </div>
                    @endif
                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Purchased: {{ $invoice->purchased_at?->format('M d, Y') }}</p>
                        <span class="badge badge-success">{{ ucfirst($invoice->status) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
