<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Users extends Component
{
    public string $search = '';

    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showForm = false;

    public ?int $viewingId = null;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = 'customer';

    public bool $is_active = true;

    public string $password = '';

    public function view(User $user): void
    {
        $this->viewingId = $user->id;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(User $user): void
    {
        $this->resetForm();
        $this->editingId = $user->id;
        $this->showForm = true;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->editingId)],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
            'is_active' => ['boolean'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        if ($this->editingId) {
            $user = User::query()->findOrFail($this->editingId);

            if ($user->id === auth()->id() && $user->role !== $validated['role']) {
                $this->addError('role', 'You cannot change your own role.');

                return;
            }

            if ($user->id === auth()->id() && ! $validated['is_active']) {
                $this->addError('is_active', 'You cannot deactivate your own account.');

                return;
            }

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null,
                'role' => $validated['role'],
                'is_active' => $validated['is_active'],
            ]);

            if ($validated['password']) {
                $user->update(['password' => $validated['password']]);
            }

            ActivityLog::record('admin.user.updated', auth()->user(), ['user' => $user->email]);
            session()->flash('status', 'User updated.');
        } else {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null,
                'role' => $validated['role'],
                'is_active' => $validated['is_active'],
                'password' => $validated['password'],
            ]);

            ActivityLog::record('admin.user.created', auth()->user(), ['user' => $user->email]);
            session()->flash('status', 'User created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(User $user): void
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');

            return;
        }

        ActivityLog::record('admin.user.deleted', auth()->user(), ['user' => $user->email]);
        $user->delete();
        session()->flash('status', 'User deleted.');
    }

    public function toggleActive(User $user): void
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot deactivate your own account.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        session()->flash('status', 'User status updated.');
    }

    #[Computed]
    public function viewingUser()
    {
        return $this->viewingId
            ? User::query()->withCount(['invoices', 'clientProjects'])->find($this->viewingId)
            : null;
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function updatedSearch(): void
    {
        $this->limit = 10;
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')))
            ->latest()
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->users->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')))
            ->count();
    }

    private function resetForm(): void
    {
        $this->reset('name', 'email', 'phone', 'role', 'is_active', 'password');
        $this->is_active = true;
        $this->role = 'customer';
    }

    public function render()
    {
        return view('livewire.admin.users');
    }
}
