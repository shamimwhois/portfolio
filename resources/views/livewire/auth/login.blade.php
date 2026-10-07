<div class="card card-pad sm:p-8">
    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Welcome back</p>
    <h1 class="mt-2 font-display text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">Sign in to your account</h1>

    <form wire:submit="login" class="mt-8 space-y-5">
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
                placeholder="••••••••"
                autocomplete="current-password"
                required
            />
            @error('password') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <label class="flex cursor-pointer select-none items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" wire:model="remember" class="h-4 w-4" />
            Remember me
        </label>

        <button type="submit" class="btn btn-primary w-full">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">
                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                Signing in...
            </span>
        </button>
    </form>

    @if (count(\App\Support\SocialAuth::available()) > 0)
        <div class="mt-6 flex items-center gap-3 text-xs text-slate-400 dark:text-slate-500">
            <span class="h-px flex-1 bg-slate-300/60 dark:bg-white/10"></span>
            <span>OR CONTINUE WITH</span>
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
        New to the panel?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Create an account</a>
    </p>
</div>
