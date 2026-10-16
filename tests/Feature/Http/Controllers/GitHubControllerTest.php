<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as OAuthUser;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\Tests\Fakes\FakeGithubProvider;

test('redirect sends the user to github', function (): void {
    $response = $this->get(route(RouteName::GITHUB_REDIRECT));

    $response->assertRedirectContains('https://github.com/login/oauth/authorize');
});

test('callback sends a member to the dashboard', function (): void {
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

    $response = $this->get(route(RouteName::GITHUB_CALLBACK));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});

test('callback sends an expired sign in back to the login page', function (): void {
    $response = $this->get(route(RouteName::GITHUB_CALLBACK, ['state' => fake()->sha1()]));

    $response
        ->assertRedirect(route(RouteName::LOGIN))
        ->assertSessionHasErrors(['github' => 'Sign-in expired. Please try again.']);
});

test('callback sends a non member back to the login page', function (): void {
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

    $response = $this->get(route(RouteName::GITHUB_CALLBACK));

    $response
        ->assertRedirect(route(RouteName::LOGIN))
        ->assertSessionHasErrors(['github' => 'You are not a member of the organization.']);
});

test('logout signs the user out', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::LOGOUT));

    $this->assertGuest(AuthenticationProvider::GUARD);
});

test('logout sends the user to the login page', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::LOGOUT));

    $response->assertRedirect(route(RouteName::LOGIN));
});
