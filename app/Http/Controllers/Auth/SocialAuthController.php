<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\SocialAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SocialAuthController extends Controller
{
    /**
     * Send the visitor to the provider's consent screen.
     */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        if (! in_array($provider, ['google', 'github'], true) || ! SocialAuth::configured($provider)) {
            return redirect()->route('login')
                ->with('error', 'Social login for '.ucfirst($provider).' is not configured.');
        }

        return redirect()->away(SocialAuth::authorizeUrl($provider));
    }

    /**
     * Handle the provider's callback — verify state, exchange the code, and
     * sign the user in (creating the account on first visit).
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        if (! in_array($provider, ['google', 'github'], true) || ! SocialAuth::configured($provider)) {
            return redirect()->route('login')
                ->with('error', 'Social login for '.ucfirst($provider).' is not configured.');
        }

        $state = (string) $request->input('state');
        $expected = (string) session()->pull('social_auth_state', '');
        $expectedProvider = (string) session()->pull('social_auth_provider', '');

        if ($state === '' || $expected === '' || ! hash_equals($expected, $state) || $expectedProvider !== $provider) {
            return redirect()->route('login')
                ->with('error', 'The social login request was invalid or expired. Please try again.');
        }

        $code = (string) $request->input('code');

        if (blank($code)) {
            return redirect()->route('login')
                ->with('error', 'The social login was cancelled.');
        }

        $profile = SocialAuth::handleCallback($provider, $code);

        if (! $profile) {
            return redirect()->route('login')
                ->with('error', 'We could not verify your '.ucfirst($provider).' account. Please try again.');
        }

        $user = User::query()->where('email', $profile['email'])->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $profile['name'],
                'email' => $profile['email'],
                'password' => Str::password(24),
                'avatar' => $profile['avatar'],
                'role' => User::ROLE_CUSTOMER,
                'is_active' => true,
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Contact support for help.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::record('auth.social_login', $user, ['provider' => $provider]);

        session()->flash('success', 'Welcome back, '.$user->name.'! You are signed in via '.ucfirst($provider).'.');

        return redirect()->intended(route('panel.dashboard'));
    }
}
