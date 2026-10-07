@php
    $unreadMessages = \App\Models\ContactMessage::query()->where('is_read', false)->count();
    $openConversations = \App\Models\SupportConversation::query()->open()->count();
@endphp

<div class="flex h-full flex-col">
    <div class="flex items-center gap-3 border-b border-slate-200/60 px-5 py-4 dark:border-white/10">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 font-display text-sm font-semibold text-white">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </span>
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Admin Console</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Admin navigation">
        @foreach (config('nav.admin') as $item)
            @if (! empty($item['children']))
                <div x-data="{ open: {{ request()->routeIs(collect($item['children'])->pluck('route')->all()) ? 'true' : 'false' }} }">
                    <button type="button" class="sidebar-item w-full" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                        <x-icon :name="$item['icon']" class="icon h-[18px] w-[18px]" />
                        <span class="flex-1 text-left">{{ $item['label'] }}</span>
                        <x-icon name="chevron-down" class="icon h-4 w-4 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition duration-150 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition duration-100 ease-in"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-cloak
                        class="ml-4 mt-1 space-y-1 border-l border-slate-200/60 pl-3 dark:border-white/10"
                    >
                        @foreach ($item['children'] as $child)
                            @php
                                $childUrl = route($child['route'], $child['params'] ?? []);
                                $childActive = request()->routeIs($child['route'])
                                    && (! isset($child['params']['section']) || request()->query('section', 'hero') === $child['params']['section']);
                            @endphp
                            <a href="{{ $childUrl }}" class="sidebar-item !py-2 text-[13px] {{ $childActive ? 'sidebar-item-active' : '' }}">
                                @if (! empty($child['icon']))
                                    <x-icon :name="$child['icon']" class="icon h-3.5 w-3.5" />
                                @endif
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <a href="{{ route($item['route']) }}" class="sidebar-item {{ request()->routeIs($item['route']) ? 'sidebar-item-active' : '' }}">
                    <x-icon :name="$item['icon']" class="icon h-[18px] w-[18px]" />
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if ($item['route'] === 'admin.messages' && $unreadMessages > 0)
                        <span class="badge badge-brand">{{ $unreadMessages }}</span>
                    @endif
                    @if ($item['route'] === 'admin.conversations' && $openConversations > 0)
                        <span class="badge badge-brand">{{ $openConversations }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <div class="space-y-1 border-t border-slate-200/60 px-3 py-4 dark:border-white/10">
        <a href="{{ route('panel.dashboard') }}" class="sidebar-item">
            <x-icon name="user" class="icon h-[18px] w-[18px]" />
            Customer Panel
        </a>
        <a href="{{ route('home') }}" class="sidebar-item">
            <x-icon name="arrow-left" class="icon h-[18px] w-[18px]" />
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
