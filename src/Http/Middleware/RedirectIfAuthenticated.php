<?php

namespace Maarheeze\Compass\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('compass');

        if (Auth::check()) {
            return redirect()->route('compass.dashboard');
        }

        return $next($request);
    }
}
