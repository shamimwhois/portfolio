<?php

namespace App\Livewire\Auth;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Rule(['required', 'email'])]
    public string $email = '';

    #[Rule(['required', 'string'])]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        $throttleKey = 'login:'.strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', 'Too many login attempts. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.');

            return;
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            $this->addError('email', 'These credentials do not match our records.');

            return;
        }

        RateLimiter::clear($throttleKey);

        session()->regenerate();

        ActivityLog::record('auth.login', auth()->user());

        $this->redirect(
            $this->safeRedirect()
                ?? (auth()->user()->isAdmin()
                    ? route('admin.dashboard')
                    : route('panel.dashboard'))
        );
    }

    /**
     * Return a safe same-origin redirect target from the `redirect` query
     * parameter, or null when it is absent or points off-site (open redirect
     * protection).
     */
    private function safeRedirect(): ?string
    {
        $redirect = request()->query('redirect');

        if (! is_string($redirect)) {
            return null;
        }

        $redirect = trim($redirect);

        // Only accept same-origin root-relative paths. Requiring a leading
        // slash (and rejecting protocol-relative '//') blocks external URLs,
        // scheme tricks and whitespace-padded attempts.
        if ($redirect === ''
            || ! str_starts_with($redirect, '/')
            || str_starts_with($redirect, '//')) {
            return null;
        }

        return $redirect;
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
