<div class="container-shell py-12 sm:py-16 lg:py-20">
    <div class="mx-auto max-w-2xl text-center">
        <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Contact</p>
        <h1 class="section-heading mt-2">Let's build something together</h1>
        <p class="section-lead mx-auto">Tell me about your project and I'll get back to you within one business day.</p>
    </div>

    <div class="mx-auto mt-12 grid max-w-5xl items-start gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-4">
            <div class="card card-pad">
                <h2 class="card-title">Direct channels</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <a href="mailto:{{ $email }}" class="flex items-center gap-3 rounded-xl border border-white/50 bg-white/40 px-4 py-3 backdrop-blur-md transition-all duration-200 hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                        <x-icon name="mail" class="icon h-5 w-5 text-brand-600 dark:text-brand-400" />
                        <span class="truncate text-slate-700 dark:text-slate-200">{{ $email }}</span>
                    </a>
                    <a href="tel:{{ $phone }}" class="flex items-center gap-3 rounded-xl border border-white/50 bg-white/40 px-4 py-3 backdrop-blur-md transition-all duration-200 hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                        <x-icon name="phone" class="icon h-5 w-5 text-brand-600 dark:text-brand-400" />
                        <span class="text-slate-700 dark:text-slate-200">{{ $phone }}</span>
                    </a>
                </div>
            </div>

            <div class="card card-pad">
                <h2 class="card-title">What happens next</h2>
                <ul class="mt-4 space-y-3">
                    @foreach (['Response within 24 hours', 'Free discovery call to align goals', 'Detailed proposal with timeline'] as $step)
                        <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                            <x-icon name="check-circle" class="icon mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                            {{ $step }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card card-pad sm:p-8">
            @if ($sent)
                <div class="py-10 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <x-icon name="check-circle" class="icon h-7 w-7" />
                    </span>
                    <h2 class="mt-5 font-display text-xl font-semibold text-slate-900 dark:text-white">Message sent</h2>
                    <p class="mx-auto mt-2 max-w-sm text-sm text-slate-600 dark:text-slate-400">Thank you for reaching out. I'll reply shortly — keep an eye on your inbox.</p>
                    <button type="button" class="btn btn-secondary btn-sm mt-6" wire:click="$set('sent', false)">Send another message</button>
                </div>
            @else
                <form wire:submit="submit" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="label">Name</label>
                            <input id="name" wire:model="name" type="text" class="input" placeholder="Your name" />
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="label">Email</label>
                            <input id="email" wire:model="email" type="email" class="input" placeholder="you@example.com" />
                            @error('email') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="label">Phone <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="phone" wire:model="phone" type="text" class="input" placeholder="+00 000 000 000" />
                        </div>
                        <div>
                            <label for="subject" class="label">Subject</label>
                            <input id="subject" wire:model="subject" type="text" class="input" placeholder="Project, service, inquiry..." />
                        </div>
                    </div>
                    <div>
                        <label for="message" class="label">Message</label>
                        <textarea id="message" wire:model="message" rows="5" class="input" placeholder="Tell me about your project..."></textarea>
                        @error('message') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <span wire:loading wire:target="submit" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        <span wire:loading.remove wire:target="submit">Send message</span>
                        <span wire:loading wire:target="submit">Sending...</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
