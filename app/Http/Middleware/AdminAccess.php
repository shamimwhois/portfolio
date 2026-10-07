<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
