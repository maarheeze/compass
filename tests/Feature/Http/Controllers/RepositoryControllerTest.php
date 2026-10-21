<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('index contains the repositories', function (): void {
    $repository = Repository::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORIES));

    $response->assertViewHas('repositories', static function (RepositoryCollection $viewRepositories) use ($repository): bool {
        return $viewRepositories->sole()->is($repository);
    });
});

test('index contains the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORIES));

    $response->assertViewHas('user', $user);
});

test('show contains the repository', function (): void {
    $repository = Repository::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORY, $repository));

    $response->assertViewHas('repository', static function (Repository $viewRepository) use ($repository): bool {
        return $viewRepository->is($repository);
    });
});

test('show contains the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORY, Repository::factory()->create()));

    $response->assertViewHas('user', $user);
});
