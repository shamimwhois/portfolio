<?php

namespace App\Livewire\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    #[Url]
    public int $plan = 0;

    #[Url]
    public int $pkg = 0;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        session()->regenerate();

        ActivityLog::record('auth.register', $user);

        session()->flash('success', 'Welcome aboard, '.$user->name.'! Your account is ready.');

        $this->redirect(
            $this->plan
                ? route('home', ['#services'])
                : ($this->pkg ? route('home', ['#stack']) : route('panel.dashboard'))
        );
    }

    public function render()
    {
        return view('livewire.auth.register', [
            'selectedPlan' => $this->plan,
            'selectedPackage' => $this->pkg,
        ]);
    }
}
