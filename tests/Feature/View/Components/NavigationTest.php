<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
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

test('tabs mark the tab of the current page as the only active one', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    $request = Request::create(route(RouteName::SITE_ENDPOINT_CHECKS, [fake()->uuid(), fake()->slug()]));
    $request->setRouteResolver(function () use ($request): Route {
        return $this->app
            ->make(Router::class)
            ->getRoutes()
            ->match($request);
    });

    $navigation = $this->app->make(Navigation::class, ['request' => $request]);

    expect($navigation->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::SITES));
});

test('tabs label each tab with its translated label', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->tabs->pluck('label')->all())
        ->toBe(['Dashboard', 'Repositories', 'Issues', 'Pull requests', 'Sites']);
});

test('dashboard url is the url of the dashboard', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->dashboardUrl)
        ->toBe(route(RouteName::DASHBOARD));
});

test('has alerts is true with an alert that is not done', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    Alert::factory()
        ->active()
        ->create();

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->hasAlerts)
        ->toBeTrue();
});

test('has alerts is false with only a done alert', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    Alert::factory()
        ->done()
        ->create();

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->hasAlerts)
        ->toBeFalse();
});

test('has alerts is false without an alert', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->hasAlerts)
        ->toBeFalse();
});

test('has alerts not muted is true with an alert that is not muted', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    Alert::factory()
        ->active()
        ->create();

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->hasAlertsNotMuted)
        ->toBeTrue();
});

test('has alerts not muted is false with only a muted alert', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    Alert::factory()
        ->active()
        ->muted()
        ->create();

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->hasAlertsNotMuted)
        ->toBeFalse();
});

test('logout url is the url of the logout', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->logoutUrl)
        ->toBe(route(RouteName::LOGOUT));
});

test('settings url is the url of the settings', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class);

    expect($navigation->settingsUrl)
        ->toBe(route(RouteName::SETTINGS_EDIT));
});
