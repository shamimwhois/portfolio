<div>
    <section class="card card-pad spotlight relative mb-6 overflow-hidden animate-fade-up">
        <div class="pointer-events-none absolute -right-16 -top-24 h-56 w-56 rounded-full bg-brand-400/25 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-48 w-48 rounded-full bg-cyan-400/15 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 md:flex-row md:items-center">
            <div class="flex-1">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Client portal</p>
                <h2 class="mt-1 font-display text-xl font-semibold text-slate-900 sm:text-2xl dark:text-white">
                    Welcome back, <span class="bg-gradient-to-r from-brand-500 to-brand-700 bg-clip-text text-transparent dark:from-brand-300 dark:to-brand-500">{{ auth()->user()->name }}</span>
                </h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Track billing, services and support below.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('home') }}#services" class="btn btn-secondary btn-sm">Browse Services</a>
                <a href="{{ route('panel.invoices') }}" class="btn btn-primary btn-sm">Billing</a>
            </div>
        </div>
    </section>

    @if (count($reminders) > 0)
        <section class="mb-6 space-y-3 animate-fade-up delay-50">
            @foreach ($reminders as $reminder)
                <a href="{{ route('panel.projects.show', $reminder) }}" class="card card-pad flex items-start gap-3 border-amber-300/60 bg-amber-500/5 transition-transform hover:-translate-y-0.5 dark:border-amber-400/20">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                        <x-icon name="bell" class="icon h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $reminder->title }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $reminder->isOverdue() ? 'Deadline passed — check in with the team.' : ($reminder->daysRemaining() !== null ? $reminder->daysRemaining().' day(s) to deadline.' : 'Reminder set for '.$reminder->remind_at->format('M d, Y g:i A').'.') }}
                        </p>
                    </div>
                    <span class="ml-auto shrink-0 text-xs font-semibold text-brand-600 dark:text-brand-400">View →</span>
                </a>
            @endforeach
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 animate-fade-up delay-75">
        @foreach ($stats as $stat)
            <div class="card card-pad spotlight group relative overflow-hidden">
                <div class="pointer-events-none absolute -right-8 -top-10 h-28 w-28 rounded-full bg-brand-400/15 blur-2xl transition-opacity duration-300 group-hover:opacity-100 opacity-60" aria-hidden="true"></div>
                <div class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-700 text-white shadow-lg shadow-brand-600/30 ring-1 ring-inset ring-white/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                        <x-icon :name="$stat['icon']" class="icon h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="stat-label">{{ $stat['label'] }}</p>
                        <p class="stat-value mt-0.5">{{ $stat['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    <section class="mt-6 grid items-start gap-6 lg:grid-cols-2 animate-fade-up delay-150">
        <div class="card card-pad">
            <h2 class="card-title">Recent Invoices</h2>
            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($recentInvoices as $invoice)
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->created_at->format('M d, Y') }} · {{ $invoice->itemLabel() }}</p>
                        </div>
                        <span class="badge {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'pending' ? 'badge-warning' : 'badge-neutral') }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No invoices yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="card-title">Latest Articles</h2>
            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($latestPosts as $post)
                    <a href="{{ route('blog.show', $post) }}" class="block py-3">
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $post->title }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $post->published_at?->format('M d, Y') }}</p>
                    </a>
                @empty
                    <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No posts yet.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
