<div>
    <section class="card card-pad spotlight relative mb-6 overflow-hidden animate-fade-up">
        <div class="pointer-events-none absolute -right-16 -top-24 h-56 w-56 rounded-full bg-brand-400/25 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-48 w-48 rounded-full bg-violet-400/15 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 md:flex-row md:items-center">
            <div class="flex-1">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Signed in</p>
                <h2 class="mt-1 font-display text-xl font-semibold text-slate-900 sm:text-2xl dark:text-white">
                    Welcome back, <span class="bg-gradient-to-r from-brand-500 to-brand-700 bg-clip-text text-transparent dark:from-brand-300 dark:to-brand-500">{{ auth()->user()->name }}</span>
                </h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    @if ($unreadMessages > 0)
                        {{ $unreadMessages }} unread message{{ $unreadMessages > 1 ? 's' : '' }} await your review.
                    @else
                        All channels are quiet. Nothing needs your attention.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">View Site</a>
                <a href="{{ route('admin.blog') }}" class="btn btn-primary btn-sm">Write Post</a>
            </div>
        </div>
    </section>

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

    <section class="mt-6 grid items-start gap-6 lg:grid-cols-3 animate-fade-up delay-150">
        <div class="card card-pad lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="card-title">Pending Orders</h2>
                <a href="{{ route('admin.invoices') }}" class="btn btn-secondary btn-sm">Manage all orders</a>
            </div>
            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($pendingOrders as $invoice)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $invoice->invoice_no }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $invoice->created_at->format('M d, Y') }} · {{ $invoice->user?->name }} · {{ $invoice->itemLabel() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($invoice->payment_method)
                                <span class="badge badge-neutral">{{ $invoice->paymentMethodLabel() }}</span>
                            @endif
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">${{ number_format($invoice->amount, 2) }}</span>
                            <span class="badge badge-warning">Pending</span>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No pending orders. All clear.</p>
                @endforelse
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="card-title">Overview</h2>
            <div class="mt-4 space-y-3 text-sm">
                @php
                    $overview = [
                        'Unread messages' => [$unreadMessages, 'text-red-600 dark:text-red-400'],
                        'Total messages' => [$totalMessages, 'text-slate-900 dark:text-white'],
                        'Pending invoices' => [$pendingInvoices, 'text-amber-600 dark:text-amber-400'],
                        'Skills configured' => [$counts['skills'] ?? 0, 'text-slate-900 dark:text-white'],
                        'Service categories' => [$counts['services'] ?? 0, 'text-slate-900 dark:text-white'],
                        'Tech packages' => [$counts['packages'] ?? 0, 'text-slate-900 dark:text-white'],
                    ];
                @endphp
                @foreach ($overview as $label => [$value, $classes])
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ $label }}</span>
                        <span class="font-semibold {{ $classes }}">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.messages') }}" class="btn btn-secondary btn-sm mt-5">Open Inbox</a>
        </div>
    </section>

    <section class="mt-6 grid items-start gap-6 lg:grid-cols-2 animate-fade-up delay-200">
        <div class="card card-pad">
            <h2 class="card-title">Recent Messages</h2>
            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($recentMessages as $message)
                    <div class="flex items-start gap-3 py-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $message->is_read ? 'bg-slate-300 dark:bg-slate-600' : 'bg-brand-600 dark:bg-brand-400' }}"></span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $message->subject ?? 'Inquiry' }} — {{ $message->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $message->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No messages yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="card-title">Activity Feed</h2>
            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($recentActivity as $log)
                    <div class="flex items-start gap-3 py-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $log->action }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="card card-pad mt-6 animate-fade-up delay-200">
        <h2 class="card-title">Quick Actions</h2>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @php
                $actions = [
                    ['route' => 'admin.projects', 'icon' => 'folder', 'label' => 'Manage Projects'],
                    ['route' => 'admin.blog', 'icon' => 'doc', 'label' => 'Write Post'],
                    ['route' => 'admin.users', 'icon' => 'users', 'label' => 'Manage Users'],
                    ['route' => 'admin.services', 'icon' => 'layers', 'label' => 'Services & Plans'],
                    ['route' => 'home', 'icon' => 'home', 'label' => 'View Site'],
                ];
            @endphp
            @foreach ($actions as $action)
                <a href="{{ route($action['route']) }}" class="btn btn-secondary btn-sm justify-start">
                    <x-icon :name="$action['icon']" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" /> {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </section>
</div>
