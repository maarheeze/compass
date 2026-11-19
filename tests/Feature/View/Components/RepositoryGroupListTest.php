<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\View\Components\RepositoryGroupList;
use Maarheeze\Compass\View\RepositoryGroup;

test('icon component is the icon of the kind of the group', function (RepositoryKind $kind, string $iconComponent): void {
    $repositoryGroupList = $this->app->make(RepositoryGroupList::class, [
        'repositoryGroup' => new RepositoryGroup($kind, new RepositoryCollection()),
    ]);

    expect($repositoryGroupList->iconComponent)
        ->toBe($iconComponent);
})->with([
    [RepositoryKind::LIBRARY, 'compass::icons.package'],
    [RepositoryKind::OTHER, 'compass::icons.file-directory'],
    [RepositoryKind::PROJECT, 'compass::icons.repo'],
]);

test('title is the title of the kind of the group', function (RepositoryKind $kind, string $title): void {
    $repositoryGroupList = $this->app->make(RepositoryGroupList::class, [
        'repositoryGroup' => new RepositoryGroup($kind, new RepositoryCollection()),
    ]);

    expect($repositoryGroupList->title)
        ->toBe($title);
})->with([
    [RepositoryKind::LIBRARY, 'Libraries'],
    [RepositoryKind::OTHER, 'Other'],
    [RepositoryKind::PROJECT, 'Projects'],
]);

test('render renders the repository group list view', function (): void {
    $repositoryGroupList = $this->app->make(RepositoryGroupList::class, [
        'repositoryGroup' => new RepositoryGroup(RepositoryKind::PROJECT, new RepositoryCollection()),
    ]);

    expect($repositoryGroupList->render()->name())
        ->toBe('compass::components.repository-group-list');
});
