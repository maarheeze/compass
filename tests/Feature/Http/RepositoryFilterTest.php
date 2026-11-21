<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Http\RepositoryFilter;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\FilterOption;

test('repositories without a filter are all the repositories', function (): void {
    $repositories = Repository::factory()
        ->count(2)
        ->create();
    $repositoryFilter = new RepositoryFilter(null, null);

    $filteredRepositories = $repositoryFilter->repositories(Repository::query())->get();

    expect($filteredRepositories->modelKeys())
        ->toEqualCanonicalizing($repositories->modelKeys());
});

test('repositories on a kind and a visibility are the repositories of both', function (): void {
    $matchingRepository = Repository::factory()->create([
        'composer_json_version' => null,
        'visibility' => RepositoryVisibility::PRIVATE,
    ]);
    Repository::factory()->create([
        'composer_json_version' => null,
        'visibility' => RepositoryVisibility::PUBLIC,
    ]);
    $libraryRepository = Repository::factory()->create([
        'composer_json_version' => fake()->sha1(),
        'visibility' => RepositoryVisibility::PRIVATE,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $libraryRepository->id,
        'type' => 'library',
    ]);
    $repositoryFilter = new RepositoryFilter(RepositoryKind::OTHER, RepositoryVisibility::PRIVATE);

    $filteredRepositories = $repositoryFilter->repositories(Repository::query())->get();

    expect($filteredRepositories->sole()->is($matchingRepository))
        ->toBeTrue();
});

test('kind filter is filtered on the kind', function (): void {
    $repositoryFilter = new RepositoryFilter(null, null);

    $kindFilter = $repositoryFilter->kindFilter(
        Repository::query(),
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    expect($kindFilter->parameterNames)
        ->toBe([RepositoryFilter::KIND_PARAMETER]);
});

test('kind filter counts only the repositories of the chosen visibility', function (): void {
    Repository::factory()->create([
        'composer_json_version' => null,
        'visibility' => RepositoryVisibility::PUBLIC,
    ]);
    Repository::factory()->create([
        'composer_json_version' => null,
        'visibility' => RepositoryVisibility::PRIVATE,
    ]);
    $repositoryFilter = new RepositoryFilter(null, RepositoryVisibility::PRIVATE);

    $kindFilter = $repositoryFilter->kindFilter(
        Repository::query(),
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    $otherOption = $kindFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
        return $filterOption->queryParameters === [RepositoryFilter::KIND_PARAMETER => RepositoryKind::OTHER->value];
    });
    expect($otherOption->count)
        ->toBe(1);
});

test('kind filter counts the repositories of every kind on a chosen kind', function (): void {
    Repository::factory()->create(['composer_json_version' => null]);
    $repositoryFilter = new RepositoryFilter(RepositoryKind::PROJECT, null);

    $kindFilter = $repositoryFilter->kindFilter(
        Repository::query(),
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    $otherOption = $kindFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
        return $filterOption->queryParameters === [RepositoryFilter::KIND_PARAMETER => RepositoryKind::OTHER->value];
    });
    expect($otherOption->count)
        ->toBe(1);
});

test('visibility filter is filtered on the visibility', function (): void {
    $repositoryFilter = new RepositoryFilter(null, null);

    $visibilityFilter = $repositoryFilter->visibilityFilter(Repository::query(), $this->app->make(Translator::class));

    expect($visibilityFilter->parameterNames)
        ->toBe([RepositoryFilter::VISIBILITY_PARAMETER]);
});

test('visibility filter counts only the repositories of the chosen kind', function (): void {
    Repository::factory()->create([
        'composer_json_version' => null,
        'visibility' => RepositoryVisibility::PUBLIC,
    ]);
    $repositoryFilter = new RepositoryFilter(RepositoryKind::PROJECT, null);

    $visibilityFilter = $repositoryFilter->visibilityFilter(Repository::query(), $this->app->make(Translator::class));

    $publicOption = $visibilityFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
        return $filterOption->queryParameters === [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PUBLIC->value];
    });
    expect($publicOption->count)
        ->toBe(0);
});

test('visibility filter counts the repositories of every visibility on a chosen visibility', function (): void {
    Repository::factory()->create(['visibility' => RepositoryVisibility::PUBLIC]);
    $repositoryFilter = new RepositoryFilter(null, RepositoryVisibility::PRIVATE);

    $visibilityFilter = $repositoryFilter->visibilityFilter(Repository::query(), $this->app->make(Translator::class));

    $publicOption = $visibilityFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
        return $filterOption->queryParameters === [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PUBLIC->value];
    });
    expect($publicOption->count)
        ->toBe(1);
});
