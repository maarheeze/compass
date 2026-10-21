<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Authentication;

use Illuminate\Contracts\Auth\Factory as AuthenticationFactory;
use Illuminate\Http\Client\Factory as HttpFactory;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as OAuthUser;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Webmozart\Assert\Assert;

use function sprintf;

readonly class GitHubAuthentication
{
    public function __construct(
        private AuthenticationFactory $authentication,
        private GithubProvider $github,
        private HttpFactory $http,
        private string $organization,
    ) {
    }

    public function authorizationUrl(): string
    {
        return $this->github->redirect()->getTargetUrl();
    }

    /**
     * @throws AuthenticationExpired
     * @throws NotAnOrganizationMember
     */
    public function callback(): User
    {
        try {
            $oauthUser = $this->github->user();
        } catch (InvalidStateException) {
            throw new AuthenticationExpired();
        }

        Assert::isInstanceOf($oauthUser, OAuthUser::class);

        if (!$this->isActiveMemberOfOrganization($oauthUser->token)) {
            throw new NotAnOrganizationMember();
        }

        $user = User::updateOrCreate(
            ['github_id' => $oauthUser->getId()],
            [
                'nickname' => $oauthUser->getNickname(),
                'name' => $oauthUser->getName(),
                'email' => $oauthUser->getEmail(),
                'avatar_url' => $oauthUser->getAvatar(),
                'github_token' => $oauthUser->token,
            ],
        );

        $this->authentication->guard(AuthenticationProvider::GUARD)->login($user, true);

        return $user;
    }

    private function isActiveMemberOfOrganization(string $token): bool
    {
        $membership = $this->http
            ->withToken($token)
            ->acceptJson()
            ->get(sprintf('https://api.github.com/user/memberships/orgs/%s', $this->organization));

        if (!$membership->successful()) {
            return false;
        }

        return $membership->json('state') === 'active';
    }
}
