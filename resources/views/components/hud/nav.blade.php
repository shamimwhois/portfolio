@php
    use App\Models\Setting;
    use App\Support\Nav;

    $items = Nav::visible(config('nav.primary'));

    $desktop = collect($items)->filter(fn ($item) => ($item['desktop'] ?? false) === true)->values();
    $account = collect($items)->filter(fn ($item) => ($item['section'] ?? null) === 'account')->values();

    $bySection = collect($items)
        ->filter(fn ($item) => ($item['section'] ?? null) !== null)
        ->groupBy(fn ($item) => $item['section'])
        ->map(fn ($group) => $group->values())
        ->all();

    $sectionLabels = ['explore' => 'Explore', 'connect' => 'Connect', 'account' => 'Account'];

    $siteName = Setting::get('site_name', 'MD SHAMIM HOSSAIN');
@endphp

<header class="glass-header sticky top-0 z-40">
    <div class="container-shell">
        <div class="flex h-16 items-center justify-between gap-4" x-data="navLock">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 font-display text-base font-semibold tracking-tight text-slate-900 dark:text-white sm:text-lg">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 font-display text-xs font-semibold text-white shadow-lg shadow-brand-600/25">
                    {{ strtoupper(substr($siteName, 0, 1)) }}
                </span>
                <span class="truncate">{{ $siteName }}</span>
            </a>

            <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Primary">
                @foreach ($desktop as $item)
                    <a href="{{ Nav::href($item) }}" class="nav-link rounded-lg px-2.5 py-1.5 text-sm font-medium">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-1 sm:gap-2">
                @if (Setting::get('theme_switcher', true))
                <x-theme-switcher />
                @endif

                <div class="hidden items-center gap-1.5 lg:flex">
                    @foreach ($account as $item)
                        @if (Nav::isLogout($item))
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="icon-btn" aria-label="Logout" title="Logout">
                                    <x-icon name="logout" class="icon h-[18px] w-[18px]" />
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route($item['route']) }}"
                                class="{{ $item['route'] === 'register' || ($item['role'] ?? 'any') !== 'guest' ? 'btn btn-primary btn-sm' : 'btn btn-ghost btn-sm' }}"
                            >
                                @if (isset($item['icon']))
                                <x-icon :name="$item['icon']" class="icon h-3.5 w-3.5" />
                                @endif
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>

                <button
                    type="button"
                    class="icon-btn lg:hidden"
                    @click="toggle"
                    :aria-expanded="mobileOpen ? 'true' : 'false'"
                    aria-label="Open site menu"
                >
                    <x-icon name="menu" class="icon h-5 w-5" x-show="!mobileOpen" />
                    <x-icon name="close" class="icon h-5 w-5" x-show="mobileOpen" x-cloak />
                </button>
            </div>

            <div
                x-show="mobileOpen"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-4"
                x-cloak
                @keydown.escape.window="close"
                class="glass-strong fixed inset-y-0 right-0 z-50 flex w-80 max-w-[85vw] flex-col rounded-l-2xl"
                role="dialog"
                aria-modal="true"
                aria-label="Site menu"
            >
                <div class="flex items-center justify-between border-b border-slate-200/60 px-5 py-4 dark:border-white/10">
                    <p class="font-display text-sm font-semibold text-slate-900 dark:text-white">Menu</p>
                    <button type="button" class="icon-btn" @click="close" aria-label="Close menu">
                        <x-icon name="close" class="icon h-5 w-5" />
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-3" aria-label="Mobile primary">
                    @foreach ($bySection as $section => $groupItems)
                        <p class="px-3 pb-1.5 pt-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            {{ $sectionLabels[$section] ?? ucfirst($section) }}
                        </p>
                        <div class="mt-1 space-y-1">
                            @foreach ($groupItems as $item)
                                @if (Nav::isLogout($item))
                                    <form method="POST" action="{{ route('logout') }}" @submit="close">
                                        @csrf
                                        <button type="submit" class="sidebar-item w-full">
                                            <x-icon name="logout" class="icon h-4 w-4" />
                                            <span>{{ $item['label'] }}</span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ Nav::href($item) }}" class="sidebar-item" @click="close">
                                        @if (isset($item['icon']))
                                        <x-icon :name="$item['icon']" class="icon h-4 w-4" />
                                        @endif
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </nav>

                <div class="border-t border-slate-200/60 px-5 py-4 dark:border-white/10">
                    <a href="{{ route('contact') }}" class="btn btn-primary w-full" @click="close">
                        <x-icon name="mail" class="icon h-4 w-4" />
                        Get in touch
                    </a>
                </div>
            </div>

            <div
                x-show="mobileOpen"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-cloak
                class="fixed inset-0 z-40 bg-slate-900/25 backdrop-blur-md lg:hidden"
                @click="close"
            ></div>
        </div>
    </div>
</header>
