<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('index contains the projects', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORIES));

    $response->assertViewHas('projects', static function (RepositoryCollection $viewProjects) use ($repository): bool {
        return $viewProjects->sole()->is($repository);
    });
});

test('index contains the libraries', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORIES));

    $response->assertViewHas('libraries', static function (RepositoryCollection $viewLibraries) use ($repository): bool {
        return $viewLibraries->sole()->is($repository);
    });
});

test('index contains the other repositories', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => null]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORIES));

    $response->assertViewHas('otherRepositories', static function (RepositoryCollection $viewOtherRepositories) use ($repository): bool {
        return $viewOtherRepositories->sole()->is($repository);
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

test('show contains the kind of the repository', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORY, $repository));

    $response->assertViewHas('kind', RepositoryKind::LIBRARY);
});

test('show contains the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORY, Repository::factory()->create()));

    $response->assertViewHas('user', $user);
});
