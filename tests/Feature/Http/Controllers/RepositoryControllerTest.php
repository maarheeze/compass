<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\PullRequest;
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

test('show contains the issues of the repository newest first', function (): void {
    $repository = Repository::factory()->create();
    $olderIssue = Issue::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $newerIssue = Issue::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-1 month'),
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORY, $repository));

    $response->assertViewHas('repository', static function (Repository $viewRepository) use ($newerIssue, $olderIssue): bool {
        return $viewRepository->issues->modelKeys() === [$newerIssue->id, $olderIssue->id];
    });
});

test('show contains the pull requests of the repository newest first', function (): void {
    $repository = Repository::factory()->create();
    $olderPullRequest = PullRequest::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $newerPullRequest = PullRequest::factory()->create([
        'repository_id' => $repository->id,
        'opened_at' => fake()->dateTimeBetween('-1 month'),
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_REPOSITORY, $repository));

    $response->assertViewHas('repository', static function (Repository $viewRepository) use ($newerPullRequest, $olderPullRequest): bool {
        return $viewRepository->pullRequests->modelKeys() === [$newerPullRequest->id, $olderPullRequest->id];
    });
});

test('delete deletes a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->delete(route(RouteName::GITHUB_REPOSITORY_DELETE, $repository));

    $this->assertModelMissing($repository);
});

test('delete sends the user back to the dashboard', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::GITHUB_REPOSITORY_DELETE, $repository));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});

test('delete is not found for a collected repository', function (): void {
    $repository = Repository::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::GITHUB_REPOSITORY_DELETE, $repository));

    $response->assertNotFound();
});

test('ignore marks a missing repository ignored', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::GITHUB_REPOSITORY_IGNORE, $repository));

    expect($repository->refresh()->missing_ignored_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('ignore sends the user back to the dashboard', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::GITHUB_REPOSITORY_IGNORE, $repository));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});
