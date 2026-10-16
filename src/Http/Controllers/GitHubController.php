<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Auth\Factory as AuthenticationFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Authentication\AuthenticationExpired;
use Maarheeze\Compass\Authentication\GitHubAuthentication;
use Maarheeze\Compass\Authentication\NotAnOrganizationMember;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Providers\AuthenticationProvider;

readonly class GitHubController
{
    public function __construct(
        private AuthenticationFactory $authentication,
        private GitHubAuthentication $githubAuthentication,
        private Redirector $redirector,
        private UrlGenerator $url,
    ) {
    }

    public function callback(): RedirectResponse
    {
        try {
            $this->githubAuthentication->callback();
        } catch (AuthenticationExpired) {
            return $this->redirector
                ->route(RouteName::LOGIN)
                ->withErrors(['github' => 'Sign-in expired. Please try again.']);
        } catch (NotAnOrganizationMember) {
            return $this->redirector
                ->route(RouteName::LOGIN)
                ->withErrors(['github' => 'You are not a member of the organization.']);
        }

        return $this->redirector->intended($this->url->route(RouteName::DASHBOARD->value));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authentication->guard(AuthenticationProvider::GUARD)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->redirector->route(RouteName::LOGIN);
    }

    public function redirect(): RedirectResponse
    {
        return $this->redirector->away($this->githubAuthentication->authorizationUrl());
    }
}
