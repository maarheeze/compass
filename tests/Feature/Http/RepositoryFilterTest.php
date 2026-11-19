<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Http\RepositoryFilter;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\FilterOption;
use Maarheeze\Compass\View\RepositoryGroup;

test('repository groups without a filter are a group per kind in display order', function (): void {
    $repositoryFilter = new RepositoryFilter(null, null);

    $repositoryGroups = $repositoryFilter->repositoryGroups(new RepositoryCollection(), $this->app->make(RepositoryKindClassifier::class));

    expect($repositoryGroups->map(static function (RepositoryGroup $repositoryGroup): RepositoryKind {
        return $repositoryGroup->kind;
    })->all())
        ->toBe([RepositoryKind::PROJECT, RepositoryKind::LIBRARY, RepositoryKind::OTHER]);
});

test('repository groups on a kind are only the group of that kind', function (): void {
    $repositoryFilter = new RepositoryFilter(RepositoryKind::LIBRARY, null);

    $repositoryGroups = $repositoryFilter->repositoryGroups(new RepositoryCollection(), $this->app->make(RepositoryKindClassifier::class));

    expect($repositoryGroups->sole()->kind)
        ->toBe(RepositoryKind::LIBRARY);
});

test('repository groups on a visibility hold only the repositories of that visibility', function (): void {
    $publicRepository = Repository::factory()->make([
        'composer_json_version' => null,
        'visibility' => RepositoryVisibility::PUBLIC,
    ]);
    $privateRepository = Repository::factory()->make([
        'composer_json_version' => null,
        'visibility' => RepositoryVisibility::PRIVATE,
    ]);
    $repositoryFilter = new RepositoryFilter(RepositoryKind::OTHER, RepositoryVisibility::PRIVATE);

    $repositoryGroups = $repositoryFilter->repositoryGroups(
        new RepositoryCollection([$publicRepository, $privateRepository]),
        $this->app->make(RepositoryKindClassifier::class),
    );

    expect($repositoryGroups->sole()->repositories->sole())
        ->toBe($privateRepository);
});

test('kind filter is filtered on the kind', function (): void {
    $repositoryFilter = new RepositoryFilter(null, null);

    $kindFilter = $repositoryFilter->kindFilter(
        new RepositoryCollection(),
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    expect($kindFilter->parameterNames)
        ->toBe([RepositoryFilter::KIND_PARAMETER]);
});

test('kind filter counts only the repositories of the chosen visibility', function (): void {
    $repositories = new RepositoryCollection([
        Repository::factory()->make([
            'composer_json_version' => null,
            'visibility' => RepositoryVisibility::PUBLIC,
        ]),
        Repository::factory()->make([
            'composer_json_version' => null,
            'visibility' => RepositoryVisibility::PRIVATE,
        ]),
    ]);
    $repositoryFilter = new RepositoryFilter(null, RepositoryVisibility::PRIVATE);

    $kindFilter = $repositoryFilter->kindFilter(
        $repositories,
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
    $repositories = new RepositoryCollection([
        Repository::factory()->make(['composer_json_version' => null]),
    ]);
    $repositoryFilter = new RepositoryFilter(RepositoryKind::PROJECT, null);

    $kindFilter = $repositoryFilter->kindFilter(
        $repositories,
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

    $visibilityFilter = $repositoryFilter->visibilityFilter(
        new RepositoryCollection(),
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    expect($visibilityFilter->parameterNames)
        ->toBe([RepositoryFilter::VISIBILITY_PARAMETER]);
});

test('visibility filter counts only the repositories of the chosen kind', function (): void {
    $repositories = new RepositoryCollection([
        Repository::factory()->make([
            'composer_json_version' => null,
            'visibility' => RepositoryVisibility::PUBLIC,
        ]),
    ]);
    $repositoryFilter = new RepositoryFilter(RepositoryKind::PROJECT, null);

    $visibilityFilter = $repositoryFilter->visibilityFilter(
        $repositories,
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    $publicOption = $visibilityFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
        return $filterOption->queryParameters === [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PUBLIC->value];
    });
    expect($publicOption->count)
        ->toBe(0);
});

test('visibility filter counts the repositories of every visibility on a chosen visibility', function (): void {
    $repositories = new RepositoryCollection([
        Repository::factory()->make(['visibility' => RepositoryVisibility::PUBLIC]),
    ]);
    $repositoryFilter = new RepositoryFilter(null, RepositoryVisibility::PRIVATE);

    $visibilityFilter = $repositoryFilter->visibilityFilter(
        $repositories,
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    $publicOption = $visibilityFilter->options->firstOrFail(static function (FilterOption $filterOption): bool {
        return $filterOption->queryParameters === [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PUBLIC->value];
    });
    expect($publicOption->count)
        ->toBe(1);
});
