<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Project;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show hands the projects ordered by repository', function (): void {
    $projects = Project::factory()->count(3)->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('projects', $projects->sortBy('repository')->values());
});

test('show hands the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('user', $user);
});
