<div>
    <div class="card card-pad">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 font-display text-xl font-semibold text-white shadow-lg shadow-brand-600/25">
                {{ strtoupper(substr($name, 0, 1)) }}
            </span>
            <div>
                <p class="font-display text-xl font-semibold text-slate-900 dark:text-white">{{ $name }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $email }}</p>
            </div>
        </div>
    </div>

    @if ($saved)
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            <x-icon name="check-circle" class="icon h-5 w-5 shrink-0" />
            <p>Profile saved successfully.</p>
        </div>
    @endif

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-2">
        <form wire:submit="saveProfile" class="card card-pad">
            <h2 class="card-title">Personal Data</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="p_name" class="label">Name</label>
                    <input id="p_name" type="text" wire:model="name" class="input" />
                    @error('name') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="p_email" class="label">Email</label>
                    <input id="p_email" type="email" wire:model="email" class="input" />
                    @error('email') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="p_phone" class="label">Phone</label>
                    <input id="p_phone" type="text" wire:model="phone" class="input" />
                </div>
                <div>
                    <label for="p_bio" class="label">Bio</label>
                    <textarea id="p_bio" rows="4" wire:model="bio" class="input"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save Profile</button>
            </div>
        </form>

        <form wire:submit="changePassword" class="card card-pad">
            <h2 class="card-title">Security</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="p_current" class="label">Current Password</label>
                    <input id="p_current" type="password" wire:model="current_password" class="input" />
                    @error('current_password') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="p_new" class="label">New Password</label>
                    <input id="p_new" type="password" wire:model="new_password" class="input" />
                    @error('new_password') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="p_confirm" class="label">Confirm Password</label>
                    <input id="p_confirm" type="password" wire:model="new_password_confirmation" class="input" />
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Update Password</button>
            </div>
        </form>
    </div>
</div>
