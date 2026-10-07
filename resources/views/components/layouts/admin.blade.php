@php
    $themeDefault = \App\Models\Setting::get('theme_default', 'system');
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $themeDefault }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="color-scheme" content="light dark" />
    <title>{{ $title ?? 'Dashboard' }} | Admin Console</title>
    <script>
      (function () {
        var stored = null;
        try {
          stored = localStorage.getItem('theme');
        } catch (e) {}
        var known = ['system', 'light', 'dark', 'light_ice', 'light_rose', 'dark_aurora', 'dark_orchid'];
        var def = stored && known.indexOf(stored) > -1 ? stored : @json($themeDefault);
        var dark = def.indexOf('dark') === 0 || (def === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.setAttribute('data-theme', def);
      })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
  </head>
  <body class="min-h-screen">
    <div class="min-h-screen lg:pl-64" x-data="{ sidebarOpen: false }">
      <aside
        class="sidebar-glass fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transition-transform duration-200 ease-in-out lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        aria-label="Admin navigation"
      >
        <x-admin.sidebar />
      </aside>

      <div
        x-show="sidebarOpen"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-30 bg-slate-900/25 backdrop-blur-md lg:hidden"
        @click="sidebarOpen = false"
      ></div>

      <div class="flex min-h-screen flex-col">
        <header class="glass-header sticky top-0 z-20">
          <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
              <button type="button" class="icon-btn lg:hidden" @click="sidebarOpen = true" aria-label="Open menu">
                <x-icon name="menu" class="icon h-5 w-5" />
              </button>
              <h1 class="truncate font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
              @if (\App\Models\Setting::get('theme_switcher', true))
              <x-theme-switcher />
              @endif

              <a href="{{ route('home') }}" class="btn btn-secondary btn-sm hidden sm:inline-flex">
                <x-icon name="external" class="icon h-3.5 w-3.5" /> View Site
              </a>

              <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button
                  type="button"
                  class="flex items-center gap-2 rounded-xl px-2 py-1.5 transition-colors hover:bg-white/60 dark:hover:bg-white/5"
                  @click="open = !open"
                  :aria-expanded="open ? 'true' : 'false'"
                >
                  <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-xs font-semibold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                  </span>
                  <span class="hidden text-sm font-medium text-slate-700 dark:text-slate-200 sm:block">
                    {{ auth()->user()->name }}
                  </span>
                  <x-icon name="chevron-down" class="icon h-4 w-4 text-slate-400" />
                </button>

                <div
                  x-show="open"
                  x-transition:enter="transition duration-150 ease-out"
                  x-transition:enter-start="opacity-0 scale-95"
                  x-transition:enter-end="opacity-100 scale-100"
                  x-transition:leave="transition duration-100 ease-in"
                  x-transition:leave-start="opacity-100 scale-100"
                  x-transition:leave-end="opacity-0 scale-95"
                  x-cloak
                  class="glass-strong absolute right-0 z-20 mt-2 w-52 rounded-2xl p-1.5"
                >
                  <a href="{{ route('home') }}" class="sidebar-item" @click="open = false">
                    <x-icon name="home" class="icon h-4 w-4" /> View Portfolio
                  </a>
                  <a href="{{ route('panel.dashboard') }}" class="sidebar-item" @click="open = false">
                    <x-icon name="user" class="icon h-4 w-4" /> Customer Panel
                  </a>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-item w-full">
                      <x-icon name="logout" class="icon h-4 w-4" /> Logout
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
          @if (session('status'))
            <div x-data="flash" class="mb-6">
              <div
                x-show="visible"
                x-transition
                x-cloak
                class="flex items-start justify-between gap-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
              >
                <p>{{ session('status') }}</p>
                <button type="button" class="shrink-0" @click="dismiss" aria-label="Dismiss">
                  <x-icon name="close" class="icon h-4 w-4" />
                </button>
              </div>
            </div>
          @endif

          @if (session('error'))
            <div x-data="flash" class="mb-6">
              <div
                x-show="visible"
                x-transition
                x-cloak
                class="flex items-start justify-between gap-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
              >
                <p>{{ session('error') }}</p>
                <button type="button" class="shrink-0" @click="dismiss" aria-label="Dismiss">
                  <x-icon name="close" class="icon h-4 w-4" />
                </button>
              </div>
            </div>
          @endif

          {{ $slot }}
        </main>

        <footer class="border-t border-slate-200/60 px-4 py-4 text-center text-xs text-slate-500 dark:border-white/10 dark:text-slate-500">
          Admin Console · {{ \App\Models\Setting::get('site_name', 'Portfolio') }}
        </footer>
      </div>
    </div>

    @livewireScripts
    {{ $scripts ?? '' }}
  </body>
</html>
