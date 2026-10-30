<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\RepositoryMissingNotice;

test('can ignore a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->canIgnore)
        ->toBeTrue();
});

test('cannot ignore a repository that is already ignored', function (): void {
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->canIgnore)
        ->toBeFalse();
});

test('delete url is the delete action of the repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->deleteUrl)
        ->toBe(route(RouteName::GITHUB_REPOSITORY_DELETE, $repository));
});

test('ignore url is the ignore action of the repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->ignoreUrl)
        ->toBe(route(RouteName::GITHUB_REPOSITORY_IGNORE, $repository));
});

test('should render when the repository is missing', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->shouldRender())
        ->toBeTrue();
});

test('should render when the repository is ignored', function (): void {
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->shouldRender())
        ->toBeTrue();
});

test('should not render when the repository is not missing', function (): void {
    $repository = Repository::factory()->create(['missing_at' => null]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->shouldRender())
        ->toBeFalse();
});

test('render renders the missing notice view', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $repositoryMissingNotice = $this->app->make(RepositoryMissingNotice::class, ['repository' => $repository]);

    expect($repositoryMissingNotice->render()->name())
        ->toBe('compass::components.missing-notice');
});
