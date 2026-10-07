<?php

namespace App\Livewire\Panel;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $bio = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public bool $saved = false;

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->bio = $user->bio ?? '';
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,'.auth()->id()],
            'phone' => ['nullable', 'string', 'max:32'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'bio' => $this->bio ?: null,
        ]);

        $this->saved = true;
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'The current password is incorrect.');

            return;
        }

        auth()->user()->update([
            'password' => $this->new_password,
        ]);

        $this->reset('current_password', 'new_password', 'new_password_confirmation');
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.panel.profile');
    }
}
