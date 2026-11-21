<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Http\RepositoryFilter;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\FilterOption;

test('kind filter options count the repositories per kind in display order', function (): void {
    $repositories = new RepositoryCollection([
        Repository::factory()->make(['composer_json_version' => null]),
        Repository::factory()
            ->make(['composer_json_version' => fake()->sha1()])
            ->setRelation('composerJson', ComposerJson::factory()->make(['type' => 'project'])),
        Repository::factory()
            ->make(['composer_json_version' => fake()->sha1()])
            ->setRelation('composerJson', ComposerJson::factory()->make(['type' => 'project'])),
    ]);

    $kindFilterOptions = $repositories->kindFilterOptions(
        $this->app->make(RepositoryKindClassifier::class),
        $this->app->make(Translator::class),
    );

    expect($kindFilterOptions->all())
        ->toEqual([
            new FilterOption('Project', 2, [RepositoryFilter::KIND_PARAMETER => RepositoryKind::PROJECT->value], null),
            new FilterOption('Library', 0, [RepositoryFilter::KIND_PARAMETER => RepositoryKind::LIBRARY->value], null),
            new FilterOption('Other', 1, [RepositoryFilter::KIND_PARAMETER => RepositoryKind::OTHER->value], null),
        ]);
});

test('of kind is the repositories of that kind', function (): void {
    $otherRepository = Repository::factory()->make(['composer_json_version' => null]);
    $projectRepository = Repository::factory()
        ->make(['composer_json_version' => fake()->sha1()])
        ->setRelation('composerJson', ComposerJson::factory()->make(['type' => 'project']));
    $repositories = new RepositoryCollection([$otherRepository, $projectRepository]);

    $projectRepositories = $repositories->ofKind(RepositoryKind::PROJECT, $this->app->make(RepositoryKindClassifier::class));

    expect($projectRepositories->sole())
        ->toBe($projectRepository);
});

test('of visibility is the repositories of that visibility', function (): void {
    $publicRepository = Repository::factory()->make(['visibility' => RepositoryVisibility::PUBLIC]);
    $privateRepository = Repository::factory()->make(['visibility' => RepositoryVisibility::PRIVATE]);
    $repositories = new RepositoryCollection([$publicRepository, $privateRepository]);

    $privateRepositories = $repositories->ofVisibility(RepositoryVisibility::PRIVATE);

    expect($privateRepositories->sole())
        ->toBe($privateRepository);
});

test('visibility filter options count the repositories per visibility in display order', function (): void {
    $repositories = new RepositoryCollection([
        Repository::factory()->make(['visibility' => RepositoryVisibility::INTERNAL]),
        Repository::factory()->make(['visibility' => RepositoryVisibility::PUBLIC]),
        Repository::factory()->make(['visibility' => RepositoryVisibility::PUBLIC]),
    ]);

    $visibilityFilterOptions = $repositories->visibilityFilterOptions($this->app->make(Translator::class));

    expect($visibilityFilterOptions->all())
        ->toEqual([
            new FilterOption('Public', 2, [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PUBLIC->value], null),
            new FilterOption('Private', 0, [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::PRIVATE->value], null),
            new FilterOption('Internal', 1, [RepositoryFilter::VISIBILITY_PARAMETER => RepositoryVisibility::INTERNAL->value], null),
        ]);
});
