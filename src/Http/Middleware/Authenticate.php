<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthenticationFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Symfony\Component\HttpFoundation\Response;

use function route;

readonly class Authenticate
{
    public function __construct(
        private AuthenticationFactory $authentication,
        private Redirector $redirector,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->authentication->guard(AuthenticationProvider::GUARD)->check()) {
            return $this->redirector->guest(route(RouteName::LOGIN));
        }

        return $next($request);
    }
}
