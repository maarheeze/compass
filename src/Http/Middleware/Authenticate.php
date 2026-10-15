<?php

namespace Maarheeze\Compass\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('compass');

        if (! Auth::check()) {
            return redirect()->guest(route('compass.login'));
        }

        return $next($request);
    }
}
