<div>
    {{-- Header --}}
    <section class="card card-pad flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Site Operations</p>
            <h2 class="card-title mt-1 text-2xl">Site Settings</h2>
            <p class="card-subtitle">
                Identity, hero copy, contact channels, and homepage content from one control surface. Every field
                here is rendered live on the public site — save once and the whole portfolio updates.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="badge badge-brand">
                <x-icon name="globe" class="icon h-3.5 w-3.5" />
                {{ \App\Models\Setting::get('site_name', 'Portfolio') }}
            </span>
        </div>
    </section>

    {{-- Overview cards --}}
    <section class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="card card-pad">
            <p class="stat-label">Identity</p>
            <p class="stat-value mt-1 text-brand-600 dark:text-brand-400">{{ $this->metrics['identity'] }}<span class="text-sm font-medium text-slate-400">/4</span></p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Core fields filled</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Social</p>
            <p class="stat-value mt-1 text-emerald-600 dark:text-emerald-400">{{ $this->metrics['social'] }}<span class="text-sm font-medium text-slate-400">/4</span></p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Channels connected</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Role Lines</p>
            <p class="stat-value mt-1 text-amber-600 dark:text-amber-400">{{ $this->metrics['lines'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Hero typewriter lines</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Stats</p>
            <p class="stat-value mt-1 text-slate-900 dark:text-white">{{ $this->metrics['stats'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">About-page counters</p>
        </div>
    </section>

    <form wire:submit="save" class="mt-6 space-y-6">
        {{-- Site identity --}}
        <section class="card card-pad">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="globe" class="icon h-4 w-4" />
                </span>
                <div>
                    <h3 class="card-title text-base">Site identity</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">The name, tagline and hero headline of your portfolio.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <x-admin.field label="Site name" name="site_name" required error="{{ $errors->first('site_name') }}" wire:model="site_name" hint="Shown in the nav, footer, and document title." />
                <x-admin.field label="Tagline" name="site_tagline" wire:model="site_tagline" hint="Short one-liner under the logo." />
            </div>
        </section>

        {{-- Hero content --}}
        <section id="section-hero" class="card card-pad">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="zap" class="icon h-4 w-4" />
                </span>
                <div>
                    <h3 class="card-title text-base">Hero content</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">The landing section visitors see first.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <x-admin.field label="Kicker" name="hero_kicker" wire:model="hero_kicker" hint="Small status pill above the headline." />
                <x-admin.field label="Title" name="hero_title" wire:model="hero_title" />
                <div class="md:col-span-2">
                    <x-admin.field label="Hero text" name="hero_text" type="textarea" rows="3" wire:model="hero_text" />
                </div>
            </div>
        </section>

        {{-- About --}}
        <section id="section-about" class="card card-pad">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="doc" class="icon h-4 w-4" />
                </span>
                <div>
                    <h3 class="card-title text-base">About</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">The personal summary rendered in the about section.</p>
                </div>
            </div>
            <div class="mt-4">
                <x-admin.field label="About text" name="about_text" type="textarea" rows="5" wire:model="about_text" />
            </div>
        </section>

        {{-- Role lines --}}
        <section class="card card-pad">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="activity" class="icon h-4 w-4" />
                </span>
                <div>
                    <h3 class="card-title text-base">Role lines</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">One line per row, cycled under your name in the hero.</p>
                </div>
            </div>
            <div class="mt-4">
                <x-admin.field label="Lines" name="typewriter_lines" type="textarea" rows="4" wire:model="typewriter_lines" hint="{{ $this->metrics['lines'] }} active line{{ $this->metrics['lines'] === 1 ? '' : 's' }}." />
            </div>
        </section>

        {{-- Stats --}}
        <section class="card card-pad">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="chart" class="icon h-4 w-4" />
                </span>
                <div>
                    <h3 class="card-title text-base">Stats</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">JSON array of { "value": "...", "label": "..." } objects.</p>
                </div>
            </div>
            <div class="mt-4">
                <x-admin.field label="Stats JSON" name="stats_json" type="textarea" rows="6" error="{{ $errors->first('stats_json') }}" wire:model="stats_json" hint="Valid JSON is required — the form blocks saving on parse errors." />
            </div>
        </section>

        {{-- Contact & social --}}
        <section class="card card-pad">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/10 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                    <x-icon name="mail" class="icon h-4 w-4" />
                </span>
                <div>
                    <h3 class="card-title text-base">Contact &amp; social</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">How visitors reach you — email, phone, and social profiles.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <x-admin.field label="Email" name="email" type="email" error="{{ $errors->first('email') }}" wire:model="email" />
                <x-admin.field label="Phone" name="phone" wire:model="phone" />
                <x-admin.field label="Facebook" name="facebook" icon="facebook" wire:model="facebook" />
                <x-admin.field label="GitHub" name="github" icon="github" wire:model="github" />
                <x-admin.field label="Telegram" name="telegram" icon="telegram" wire:model="telegram" />
                <x-admin.field label="WhatsApp" name="whatsapp" icon="whatsapp" wire:model="whatsapp" />
            </div>
        </section>

        {{-- Save bar --}}
        <div class="sticky bottom-4 z-10 flex justify-end">
            <div class="glass-strong flex items-center gap-3 rounded-2xl px-4 py-3 shadow-xl">
                <p class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">Changes go live on the public site immediately.</p>
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                    <x-icon name="check" class="icon h-3.5 w-3.5" /> Save Settings
                </button>
            </div>
        </div>
    </form>

    @if (in_array(request()->query('section'), ['hero', 'about'], true))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('section-{{ request()->query('section') }}');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    el.classList.add('ring-2', 'ring-brand-400/60', 'dark:ring-brand-300/50');
                    setTimeout(() => el.classList.remove('ring-2', 'ring-brand-400/60', 'dark:ring-brand-300/50'), 3000);
                }
            });
        </script>
    @endif
</div>
