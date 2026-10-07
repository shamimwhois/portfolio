@php use App\Models\Setting; @endphp
@php
    $siteName = Setting::get('site_name', 'MD SHAMIM HOSSAIN');
    $themeDefault = Setting::get('theme_default', 'system');
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $themeDefault }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="color-scheme" content="light dark" />
    <title>{{ $title ?? 'Authenticate' }} | {{ $siteName }}</title>
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
  <body class="flex min-h-screen flex-col">
    <x-hud.nav />

    <main class="flex flex-1 items-center justify-center px-4 py-12">
      <div class="w-full max-w-md">
        <div class="mb-8 text-center">
          <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600 font-display text-lg font-semibold text-white shadow-lg shadow-brand-600/25">
            {{ strtoupper(substr(Setting::get('site_name', 'MS'), 0, 2)) }}
          </span>
        </div>
        {{ $slot }}
      </div>
    </main>

    <footer class="border-t border-slate-200/60 py-6 dark:border-white/10">
      <div class="container-shell text-center text-xs text-slate-500 dark:text-slate-400">
        &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
      </div>
    </footer>

    @livewireScripts
  </body>
</html>
