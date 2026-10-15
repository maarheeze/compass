<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function redirect;
use function route;

class Authenticate
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('compass');

        if (! Auth::check()) {
            return redirect()->guest(route('compass.login'));
        }

        return $next($request);
    }
}
