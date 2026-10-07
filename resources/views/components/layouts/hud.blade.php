@php use App\Models\Setting; @endphp
@php
    $siteName = Setting::get('site_name', 'MD SHAMIM HOSSAIN');
    $siteTagline = Setting::get('site_tagline', 'Senior Laravel & PHP Developer');
    $seo = \App\Support\Seo::forRequest();
    $metaTitle = $seo['meta_title'] ?? null;
    $metaDescription = $metaDescription ?? ($seo['meta_description'] ?? Setting::get('site_tagline', 'Senior Laravel & PHP Developer Portfolio'));
    $themeDefault = request()->query('preview_theme', Setting::get('theme_default', 'system'));

    // Clamp the preview query param to a known theme.
    $known = ['system', 'light', 'dark', 'light_ice', 'light_rose', 'dark_aurora', 'dark_orchid'];
    if (request()->has('preview_theme') && ! in_array($themeDefault, $known, true)) {
        $themeDefault = Setting::get('theme_default', 'system');
    }
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $themeDefault }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="description" content="{{ $metaDescription }}" />
    <meta property="og:title" content="{{ $metaTitle ?? $siteName }}" />
    <meta property="og:description" content="{{ $metaDescription }}" />
    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}" />
    @if (! empty($seo['og_image']))
      <meta property="og:image" content="{{ $seo['og_image'] }}" />
    @endif
    <meta name="color-scheme" content="light dark" />
    @if (! empty($seo['robots']))
      <meta name="robots" content="{{ $seo['robots'] }}" />
    @endif
    <title>{{ $title ?? $metaTitle ?? $siteName }} | {{ $siteTagline }}</title>
    <script>
      (function () {
        var known = ['system', 'light', 'dark', 'light_ice', 'light_rose', 'dark_aurora', 'dark_orchid'];
        var qs = new URLSearchParams(window.location.search).get('preview_theme');
        var def = qs && known.indexOf(qs) > -1 ? qs : (function() {
          try { var s = localStorage.getItem('theme'); return s && known.indexOf(s) > -1 ? s : null; } catch(e) { return null; }
        })() || @json($themeDefault);
        var dark = def.indexOf('dark') === 0 || (def === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.setAttribute('data-theme', def);
      })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! \App\Support\Theme::styleTag($themeDefault) !!}
    @livewireStyles
    {{ $head ?? '' }}
  </head>
  <body class="flex min-h-screen flex-col" x-data>
    <x-hud.nav />

    <main class="flex-1">
      @if (session('status'))
        <div class="container-shell pt-6" x-data="flash">
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
        <div class="container-shell pt-6" x-data="flash">
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

      @if (Setting::get('ai_enabled', true))
        <livewire:support.assistant />
      @endif
    </main>

    <footer class="border-t border-slate-200/60 bg-white/40 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.02]" id="contact">
      <div class="container-shell py-14">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
          <div class="lg:col-span-2">
            <a href="{{ route('home') }}" class="font-display text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
              {{ $siteName }}
            </a>
            <p class="mt-3 max-w-sm text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $siteTagline }}</p>
            <div class="mt-5 flex items-center gap-3">
              @foreach (\App\Support\Social::connected() as $key => $channel)
                <a
                  href="{{ $channel['url'] }}"
                  target="_blank"
                  rel="noopener"
                  class="icon-btn"
                  aria-label="{{ $channel['label'] }}"
                  title="{{ $channel['label'] }}"
                >
                  <x-icon :name="$channel['icon']" class="icon h-5 w-5" />
                </a>
              @endforeach
            </div>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Explore</p>
            <ul class="mt-4 space-y-3">
              @foreach (\App\Support\Nav::visible(config('nav.footer')) as $item)
                <li>
                  <a href="{{ \App\Support\Nav::href($item) }}" class="nav-link">{{ $item['label'] }}</a>
                </li>
              @endforeach
            </ul>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Get in touch</p>
            <ul class="mt-4 space-y-3 text-sm">
              <li class="flex items-center gap-3">
                <x-icon name="mail" class="icon h-4 w-4 text-slate-400" />
                <a href="mailto:{{ Setting::get('email', 'hello@example.com') }}" class="nav-link">{{ Setting::get('email', 'hello@example.com') }}</a>
              </li>
              <li class="flex items-center gap-3">
                <x-icon name="phone" class="icon h-4 w-4 text-slate-400" />
                <a href="tel:{{ Setting::get('phone', '') }}" class="nav-link">{{ Setting::get('phone', '') }}</a>
              </li>
              <li class="flex items-center gap-3">
                <x-icon name="send" class="icon h-4 w-4 text-slate-400" />
                <a href="{{ route('contact') }}" class="nav-link">Send a message</a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="border-t border-slate-200/60 py-6 dark:border-white/10">
        <div class="container-shell flex flex-col items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400 sm:flex-row">
          <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
          <p>Built with Laravel, Livewire &amp; Tailwind CSS.</p>
        </div>
      </div>
    </footer>

    <div x-data="modal" @open-project.window="openModal($event.detail)">
      <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-[60] flex items-end justify-center bg-slate-900/30 backdrop-blur-md sm:items-center sm:p-4"
        @click="closeModal"
      >
        <div
          class="glass-strong w-full overflow-hidden rounded-t-2xl sm:max-w-lg sm:rounded-2xl"
          @click.stop
          @keydown.escape.window="onEscape"
        >
          <div class="relative">
            <div class="aspect-[16/9] w-full bg-white/40 dark:bg-white/5" x-show="item?.thumbnail" x-cloak>
              <img :src="item?.thumbnail" :alt="item?.title" class="h-full w-full object-cover" />
            </div>
            <div class="flex items-center justify-between gap-4 px-6 pt-5">
              <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="item?.title"></p>
              <button type="button" class="icon-btn shrink-0" @click="closeModal" aria-label="Close">
                <x-icon name="close" class="icon h-5 w-5" />
              </button>
            </div>
            <div class="px-6 pb-6 pt-3">
              <span x-show="item?.status" x-cloak class="badge badge-brand mb-3" x-text="item?.status"></span>
              <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400" x-text="item?.description"></p>
              <div class="mt-4 flex flex-wrap gap-2" x-show="item?.tech" x-cloak>
                <template x-for="t in item?.tech" :key="t">
                  <span class="badge badge-neutral" x-text="t"></span>
                </template>
              </div>
              <div class="mt-6 flex flex-wrap gap-3" x-show="item?.link" x-cloak>
                <a :href="item?.link" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                  <x-icon name="external" class="icon h-3.5 w-3.5" /> Visit project
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div x-data="modal" @open-package.window="openModal($event.detail)">
      <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-[60] flex items-end justify-center bg-slate-900/30 backdrop-blur-md sm:items-center sm:p-4"
        @click="closeModal"
      >
        <div
          class="glass-strong w-full overflow-hidden rounded-t-2xl sm:max-w-xl sm:rounded-2xl"
          @click.stop
          @keydown.escape.window="onEscape"
        >
          <div class="flex items-center justify-between gap-4 px-6 pt-5">
            <div class="min-w-0">
              <p class="truncate font-display text-lg font-semibold text-slate-900 dark:text-white" x-text="item?.title"></p>
              <div class="mt-1.5 flex flex-wrap gap-2">
                <span x-show="item?.version" x-cloak class="badge badge-neutral" x-text="'v' + item?.version"></span>
                <span x-show="item?.type" x-cloak class="badge badge-neutral" x-text="item?.type"></span>
              </div>
            </div>
            <button type="button" class="icon-btn shrink-0" @click="closeModal" aria-label="Close">
              <x-icon name="close" class="icon h-5 w-5" />
            </button>
          </div>
          <div class="px-6 pb-6 pt-3">
            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400" x-text="item?.description"></p>

            <p x-show="item?.features?.length" x-cloak class="mt-5 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Features</p>
            <ul class="mt-3 space-y-2" x-show="item?.features?.length" x-cloak>
              <template x-for="(f, i) in item?.features" :key="i">
                <li class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-400">
                  <x-icon name="check" class="icon mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                  <span x-text="f"></span>
                </li>
              </template>
            </ul>

            <p x-show="item?.install_commands?.length" x-cloak class="mt-5 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Installation</p>
            <div class="mt-3 space-y-2" x-show="item?.install_commands?.length" x-cloak>
              <template x-for="(cmd, i) in item?.install_commands" :key="i">
                <div class="rounded-xl border border-white/50 bg-white/40 px-3 py-2 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                  <code class="font-mono text-xs text-slate-700 dark:text-slate-300" x-text="cmd"></code>
                </div>
              </template>
            </div>

            <div class="mt-6 flex flex-wrap gap-3" x-show="item?.repoUrl || item?.docsUrl" x-cloak>
              <a x-show="item?.repoUrl" :href="item?.repoUrl" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                <x-icon name="github" class="icon h-3.5 w-3.5" /> Repository
              </a>
              <a x-show="item?.docsUrl" :href="item?.docsUrl" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                <x-icon name="doc" class="icon h-3.5 w-3.5" /> Documentation
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    @livewireScripts
    {{ $scripts ?? '' }}
  </body>
</html>
