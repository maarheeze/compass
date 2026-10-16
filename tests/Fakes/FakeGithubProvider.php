<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fakes;

use Illuminate\Http\Request;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as OAuthUser;

use function fake;

class FakeGithubProvider extends GithubProvider
{
    public function __construct(
        Request $request,
        private readonly OAuthUser $oauthUser,
    ) {
        parent::__construct($request, fake()->uuid(), fake()->sha1(), fake()->url());
    }

    public function user(): OAuthUser
    {
        return $this->oauthUser;
    }
}
