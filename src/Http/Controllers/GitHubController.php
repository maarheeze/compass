<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Auth\Factory as AuthenticationFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Authentication\AuthenticationExpired;
use Maarheeze\Compass\Authentication\GitHubAuthentication;
use Maarheeze\Compass\Authentication\NotAnOrganizationMember;
use Maarheeze\Compass\Authentication\NoVerifiedEmail;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Webmozart\Assert\Assert;

readonly class GitHubController
{
    public function __construct(
        private AuthenticationFactory $authentication,
        private GitHubAuthentication $githubAuthentication,
        private Redirector $redirector,
        private Translator $translator,
        private UrlGenerator $urlGenerator,
    ) {
    }

    public function callback(): RedirectResponse
    {
        try {
            $this->githubAuthentication->callback();
        } catch (AuthenticationExpired) {
            $message = Assert::string($this->translator->get('compass::authentication.expired'));

            return $this->redirector
                ->route(RouteName::LOGIN)
                ->withErrors(['github' => $message]);
        } catch (NoVerifiedEmail) {
            $message = Assert::string($this->translator->get('compass::authentication.no_verified_email'));

            return $this->redirector
                ->route(RouteName::LOGIN)
                ->withErrors(['github' => $message]);
        } catch (NotAnOrganizationMember) {
            $message = Assert::string($this->translator->get('compass::authentication.not_an_organization_member'));

            return $this->redirector
                ->route(RouteName::LOGIN)
                ->withErrors(['github' => $message]);
        }

        return $this->redirector->intended($this->urlGenerator->route(RouteName::DASHBOARD->value));
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
