<?php

declare(strict_types=1);

use Illuminate\Contracts\Config\Repository as ConfigurationRepository;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('guests are sent to the login page', function (): void {
    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertRedirect(route(RouteName::LOGIN));
});

test('guests leave the default guard of the host alone', function (): void {
    $configuration = $this->app->make(ConfigurationRepository::class);
    $defaultGuard = $configuration->get('auth.defaults.guard');

    $this->get(route(RouteName::DASHBOARD));

    expect($configuration->get('auth.defaults.guard'))
        ->toBe($defaultGuard);
});

test('signed in users pass through', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertOk();
});
