<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Factory as AuthenticationFactory;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Laravel\Socialite\Two\GithubProvider;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('guard signs in compass users', function (): void {
    $user = User::factory()->create();
    $guard = $this->app->make(AuthenticationFactory::class)->guard(AuthenticationProvider::GUARD);

    $guard->loginUsingId($user->getKey());

    $this->assertAuthenticatedAs($user, AuthenticationProvider::GUARD);
});

test('github provider sends github back to the callback route', function (): void {
    $request = $this->app->make(Request::class);
    $request->setLaravelSession($this->app->make(SessionManager::class)->driver());

    $github = $this->app->make(GithubProvider::class);
    $authorizationUrl = $github->redirect()->getTargetUrl();

    parse_str((string) parse_url($authorizationUrl, PHP_URL_QUERY), $query);
    expect($query['redirect_uri'])->toBe(route(RouteName::GITHUB_CALLBACK));
});

test('github provider uses the configured client id', function (): void {
    $clientId = fake()->uuid();
    $this->app->make(Repository::class)->set('compass.github.client_id', $clientId);

    $request = $this->app->make(Request::class);
    $request->setLaravelSession($this->app->make(SessionManager::class)->driver());

    $github = $this->app->make(GithubProvider::class);
    $authorizationUrl = $github->redirect()->getTargetUrl();

    parse_str((string) parse_url($authorizationUrl, PHP_URL_QUERY), $query);
    expect($query['client_id'])->toBe($clientId);
});
