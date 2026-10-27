<?php

declare(strict_types=1);

use Illuminate\Contracts\Config\Repository as ConfigurationRepository;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('signed in users are sent to the dashboard', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::LOGIN));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});

test('guests pass through', function (): void {
    $response = $this->get(route(RouteName::LOGIN));

    $response->assertOk();
});

test('guests leave the default guard of the host alone', function (): void {
    $configuration = $this->app->make(ConfigurationRepository::class);
    $defaultGuard = $configuration->get('auth.defaults.guard');

    $this->get(route(RouteName::LOGIN));

    expect($configuration->get('auth.defaults.guard'))
        ->toBe($defaultGuard);
});
