<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        ActivityLog::record('auth.logout', $user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        session()->flash('status', 'You have been signed out.');

        return redirect()->route('home');
    }
}