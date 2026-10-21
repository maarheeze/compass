<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the repositories with a composer warning', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'is_active' => true,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('repositoriesWithComposerWarning', static function (RepositoryCollection $viewRepositories) use ($repository): bool {
        return $viewRepositories->sole()->is($repository);
    });
});

test('show contains the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('user', $user);
});
