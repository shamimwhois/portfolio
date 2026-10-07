<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! auth()->user()->is_active) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This account has been deactivated.']);
        }

        return $next($request);
    }
}
