<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as OAuthUser;
use Maarheeze\Compass\Authentication\AuthenticationExpired;
use Maarheeze\Compass\Authentication\GitHubAuthentication;
use Maarheeze\Compass\Authentication\NotAnOrganizationMember;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\Tests\Fakes\FakeGithubProvider;

test('authorization url points to github', function (): void {
    $request = $this->app->make(Request::class);
    $request->setLaravelSession($this->app->make(SessionManager::class)->driver());

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $authorizationUrl = $githubAuthentication->authorizationUrl();

    expect($authorizationUrl)->toStartWith('https://github.com/login/oauth/authorize?');
});

test('authorization url carries the state kept in the session', function (): void {
    $session = $this->app->make(SessionManager::class)->driver();
    $request = $this->app->make(Request::class);
    $request->setLaravelSession($session);

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $authorizationUrl = $githubAuthentication->authorizationUrl();

    parse_str((string) parse_url($authorizationUrl, PHP_URL_QUERY), $query);
    expect($query['state'])->toBe($session->get('state'));
});

test('callback signs in an active member', function (): void {
    $oauthUser = new OAuthUser()
        ->setToken(fake()->sha1())
        ->map([
            'id' => fake()->randomNumber(),
            'nickname' => fake()->userName(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'avatar' => fake()->imageUrl(),
        ]);
    $this->app->instance(GithubProvider::class, new FakeGithubProvider($this->app->make(Request::class), $oauthUser));

    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(['state' => 'active'])]);

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $user = $githubAuthentication->callback();

    $this->assertAuthenticatedAs($user, AuthenticationProvider::GUARD);
});

test('callback stores the github user', function (): void {
    $oauthUser = new OAuthUser()
        ->setToken(fake()->sha1())
        ->map([
            'id' => fake()->randomNumber(),
            'nickname' => fake()->userName(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'avatar' => fake()->imageUrl(),
        ]);
    $this->app->instance(GithubProvider::class, new FakeGithubProvider($this->app->make(Request::class), $oauthUser));

    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(['state' => 'active'])]);

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $githubAuthentication->callback();

    $this->assertDatabaseHas(User::class, [
        'github_id' => $oauthUser->getId(),
        'nickname' => $oauthUser->getNickname(),
        'name' => $oauthUser->getName(),
        'email' => $oauthUser->getEmail(),
        'avatar_url' => $oauthUser->getAvatar(),
    ]);
});

test('callback checks the membership of the organization with the user token', function (): void {
    $organization = fake()->slug();
    $this->app->make(Repository::class)->set('compass.organization', $organization);

    $oauthUser = new OAuthUser()
        ->setToken(fake()->sha1())
        ->map([
            'id' => fake()->randomNumber(),
            'nickname' => fake()->userName(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'avatar' => fake()->imageUrl(),
        ]);
    $this->app->instance(GithubProvider::class, new FakeGithubProvider($this->app->make(Request::class), $oauthUser));

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['state' => 'active'])]);

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $githubAuthentication->callback();

    $http->assertSent(static function (HttpRequest $request) use ($oauthUser, $organization): bool {
        return $request->url() === sprintf('https://api.github.com/user/memberships/orgs/%s', $organization)
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $oauthUser->token));
    });
});

test('callback refuses a pending member', function (): void {
    $oauthUser = new OAuthUser()
        ->setToken(fake()->sha1())
        ->map([
            'id' => fake()->randomNumber(),
            'nickname' => fake()->userName(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'avatar' => fake()->imageUrl(),
        ]);
    $this->app->instance(GithubProvider::class, new FakeGithubProvider($this->app->make(Request::class), $oauthUser));

    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(['state' => 'pending'])]);

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $githubAuthentication->callback();
})->throws(NotAnOrganizationMember::class);

test('callback refuses a non member', function (): void {
    $oauthUser = new OAuthUser()
        ->setToken(fake()->sha1())
        ->map([
            'id' => fake()->randomNumber(),
            'nickname' => fake()->userName(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'avatar' => fake()->imageUrl(),
        ]);
    $this->app->instance(GithubProvider::class, new FakeGithubProvider($this->app->make(Request::class), $oauthUser));

    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $githubAuthentication->callback();
})->throws(NotAnOrganizationMember::class);

test('callback refuses a state that is not in the session', function (): void {
    $request = $this->app->make(Request::class);
    $request->setLaravelSession($this->app->make(SessionManager::class)->driver());
    $this->app->instance(
        GithubProvider::class,
        new GithubProvider($request, fake()->uuid(), fake()->sha1(), fake()->url()),
    );

    $githubAuthentication = $this->app->make(GitHubAuthentication::class);
    $githubAuthentication->callback();
})->throws(AuthenticationExpired::class);
