<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('guests are sent to the login page', function (): void {
    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertRedirect(route(RouteName::LOGIN));
});

test('signed in users pass through', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertOk();
});
