<?php

declare(strict_types=1);

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
