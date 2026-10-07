<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative w-full sm:max-w-sm">
            <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" class="input pl-10" placeholder="Search users..." aria-label="Search users" />
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New User
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit User' : 'New User' }}" subtitle="Manage accounts, roles and access" icon="user" size="md" close-method="cancel">
            <form wire:submit="save" id="user-form" class="grid gap-4 md:grid-cols-2">
                <x-admin.field label="Name" name="name" placeholder="Full name" icon="user" required error="{{ $errors->first('name') }}" wire:model="name" />
                <x-admin.field label="Email" name="email" type="email" placeholder="user@example.com" icon="mail" required error="{{ $errors->first('email') }}" wire:model="email" />
                <x-admin.field label="Phone" name="phone" placeholder="+00 000 000 000" icon="phone" wire:model="phone" />
                <x-admin.field label="Role" name="role" type="select" icon="shield" error="{{ $errors->first('role') }}" wire:model="role">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </x-admin.field>
                <div class="md:col-span-2">
                    <x-admin.field label="{{ $editingId ? 'New password' : 'Password' }}" name="password" type="password" placeholder="{{ $editingId ? 'Leave blank to keep current password' : 'Min. 8 characters' }}" icon="lock" :required="! $editingId" hint="{{ $editingId ? 'Leave blank to keep the current password' : 'Choose a secure password' }}" error="{{ $errors->first('password') }}" wire:model="password" />
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 pb-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <input type="checkbox" wire:model="is_active" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-white/10" /> Active account
                </label>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="user-form" save-label="{{ $editingId ? 'Update user' : 'Create user' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->users->take($this->limit) as $user)
                    <tr>
                        <td class="font-medium text-slate-900 dark:text-white">{{ $user->name }}</td>
                        <td class="text-slate-500 dark:text-slate-400">{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->isAdmin() ? 'badge-brand' : 'badge-neutral' }}">{{ ucfirst($user->role) }}</span>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-right">
                            <x-admin.actions :view-method="'view'" :view-id="$user->id" :edit="$user->id" :delete="$user->id" delete-message="Delete this user?" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($this->hasMore)
        <div class="mt-6 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more users
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
        </div>
    @endif

    @if ($this->viewingUser)
        @php $vu = $this->viewingUser; @endphp
        <x-admin.modal title="{{ $vu->name }}" subtitle="Account details" icon="user" size="md" close-method="closeView">
            <div class="space-y-5">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-lg font-bold text-white shadow-lg shadow-brand-600/25">
                        {{ strtoupper(substr($vu->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $vu->name }}</p>
                        <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $vu->email }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="badge {{ $vu->isAdmin() ? 'badge-brand' : 'badge-neutral' }}">{{ ucfirst($vu->role) }}</span>
                    <span class="badge {{ $vu->is_active ? 'badge-success' : 'badge-danger' }}">{{ $vu->is_active ? 'Active' : 'Inactive' }}</span>
                </div>

                <dl class="space-y-2.5 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="shrink-0 text-slate-500 dark:text-slate-400">Email</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $vu->email }}</dd>
                    </div>
                    @if ($vu->phone)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="shrink-0 text-slate-500 dark:text-slate-400">Phone</dt>
                            <dd class="text-slate-700 dark:text-slate-300">{{ $vu->phone }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-4">
                        <dt class="shrink-0 text-slate-500 dark:text-slate-400">Joined</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $vu->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="shrink-0 text-slate-500 dark:text-slate-400">Orders</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $vu->invoices_count }} invoice(s)</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="shrink-0 text-slate-500 dark:text-slate-400">Client projects</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $vu->client_projects_count }}</dd>
                    </div>
                </dl>
            </div>
            <x-slot:footer>
                <div class="flex justify-end">
                    <button type="button" class="btn btn-primary btn-sm" wire:click="closeView">
                        <x-icon name="check" class="icon h-3.5 w-3.5" /> Close
                    </button>
                </div>
            </x-slot:footer>
        </x-admin.modal>
    @endif
</div>
