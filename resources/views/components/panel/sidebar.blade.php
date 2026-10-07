@php
    $user = auth()->user();

    $items = \App\Support\Nav::visible(config('nav.panel'));
@endphp

<div class="flex h-full flex-col">
    <div class="flex items-center gap-3 border-b border-slate-200/60 px-5 py-4 dark:border-white/10">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 font-display text-sm font-semibold text-white">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </span>
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->isAdmin() ? 'Administrator' : 'Client Panel' }}</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Panel navigation">
        @foreach ($items as $item)
            <a href="{{ route($item['route']) }}" class="sidebar-item {{ request()->routeIs($item['route']) ? 'sidebar-item-active' : '' }}">
                <x-icon :name="$item['icon']" class="icon h-[18px] w-[18px]" />
                <span class="flex-1">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="space-y-1 border-t border-slate-200/60 px-3 py-4 dark:border-white/10">
        <a href="{{ route('home') }}" class="sidebar-item">
            <x-icon name="home" class="icon h-[18px] w-[18px]" />
            Back to Portfolio
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-item w-full">
                <x-icon name="logout" class="icon h-[18px] w-[18px]" />
                Logout
            </button>
        </form>
    </div>
</div>
