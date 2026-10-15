<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Maarheeze\Compass\Models\User;

use function redirect;
use function route;

class GitHubController
{
    /**
     * Scopes needed to check organisation membership, including private
     * memberships, and to read the user's email address.
     */
    private const array SCOPES = ['read:org', 'user:email'];

    public function redirect(): RedirectResponse
    {
        return $this->github()
            ->scopes(self::SCOPES)
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        /** @var SocialiteUser $github */
        $github = $this->github()->user();

        $user = User::updateOrCreate(
            ['github_id' => $github->getId()],
            [
                'nickname' => $github->getNickname(),
                'name' => $github->getName(),
                'email' => $github->getEmail(),
                'avatar_url' => $github->getAvatar(),
                'github_token' => $github->token,
            ],
        );

        Auth::guard('compass')->login($user, remember: true);

        return redirect()->intended(route('compass.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('compass')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('compass.login');
    }

    private function github(): AbstractProvider
    {
        return Socialite::buildProvider(GithubProvider::class, [
            ...Config::array('compass.github'),
            'redirect' => route('compass.auth.github.callback'),
        ]);
    }
}
