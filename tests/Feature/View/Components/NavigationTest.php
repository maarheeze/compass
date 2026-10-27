<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Components\Navigation;

test('user is the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->user->is($user))
        ->toBeTrue();
});

test('last collected at is when the last collection was collected', function (): void {
    $repositoryCollectedAt = CarbonImmutable::instance(fake()->dateTime());
    Repository::factory()->create(['collected_at' => $repositoryCollectedAt]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->lastCollectedAt?->getTimestamp())
        ->toBe($repositoryCollectedAt->getTimestamp());
});

test('tabs mark the tab of the current page as the only active one', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    $request = Request::create(route(RouteName::FORGE_SITE_ENDPOINT_CHECKS, [fake()->uuid(), fake()->slug()]));
    $request->setRouteResolver(function () use ($request): Route {
        return $this->app
            ->make(Router::class)
            ->getRoutes()
            ->match($request);
    });

    $navigation = $this->app->make(Navigation::class, ['request' => $request]);

    expect($navigation->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::FORGE_SITES));
});

test('dashboard url is the url of the dashboard', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->dashboardUrl)
        ->toBe(route(RouteName::DASHBOARD));
});

test('sync url is the url of the sync', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->syncUrl)
        ->toBe(route(RouteName::SYNC));
});

test('logout url is the url of the logout', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->logoutUrl)
        ->toBe(route(RouteName::LOGOUT));
});
