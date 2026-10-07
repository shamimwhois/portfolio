<div class="card card-pad sm:p-8">
    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Get started</p>
    <h1 class="mt-2 font-display text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">Create your account</h1>

    @if ($selectedPlan || $selectedPackage)
        <div class="mt-5 flex items-start gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800 dark:border-brand-800 dark:bg-brand-500/10 dark:text-brand-300">
            <x-icon name="check-circle" class="icon mt-0.5 h-4 w-4 shrink-0" />
            <p>
                <strong>{{ $selectedPlan ? 'Plan' : 'Package' }}</strong> selected — finish signup and manage your order from the panel.
            </p>
        </div>
    @endif

    <form wire:submit="register" class="mt-8 space-y-5">
        <div>
            <label for="name" class="label">Full Name</label>
            <input
                id="name"
                type="text"
                wire:model="name"
                class="input"
                placeholder="Your name"
                autocomplete="name"
                required
            />
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input
                id="email"
                type="email"
                wire:model="email"
                class="input"
                placeholder="you@example.com"
                autocomplete="email"
                required
            />
            @error('email') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input
                id="password"
                type="password"
                wire:model="password"
                class="input"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                required
            />
            @error('password') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Confirm Password</label>
            <input
                id="password_confirmation"
                type="password"
                wire:model="password_confirmation"
                class="input"
                placeholder="Repeat password"
                autocomplete="new-password"
                required
            />
        </div>

        <button type="submit" class="btn btn-primary w-full">
            <span wire:loading.remove wire:target="register">Create account</span>
            <span wire:loading wire:target="register">
                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                Creating account...
            </span>
        </button>
    </form>

    @if (count(\App\Support\SocialAuth::available()) > 0)
        <div class="mt-6 flex items-center gap-3 text-xs text-slate-400 dark:text-slate-500">
            <span class="h-px flex-1 bg-slate-300/60 dark:bg-white/10"></span>
            <span>OR SIGN UP WITH</span>
            <span class="h-px flex-1 bg-slate-300/60 dark:bg-white/10"></span>
        </div>

        <div class="mt-4 grid gap-2.5">
            @foreach (\App\Support\SocialAuth::available() as $provider => $config)
                <a
                    href="{{ route('auth.redirect', $provider) }}"
                    class="flex items-center justify-center gap-2.5 rounded-xl border px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm backdrop-blur-md transition-all duration-200 hover:-translate-y-px dark:text-slate-200"
                    style="border-color: var(--glass-border); background-color: var(--glass-bg);"
                >
                    <x-icon :name="$provider === 'google' ? 'google' : 'github'" class="icon h-4 w-4" />
                    {{ $provider === 'google' ? 'Continue with Google' : 'Continue with GitHub' }}
                </a>
            @endforeach
        </div>
    @endif

    <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Sign in instead</a>
    </p>
</div>
