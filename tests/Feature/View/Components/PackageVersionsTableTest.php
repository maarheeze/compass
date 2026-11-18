<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyser\Composer\PackageVersionCount;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\PackageVersionsTable;

test('render renders the package versions table view', function (): void {
    $packageVersionsTable = $this->app->make(PackageVersionsTable::class, [
        'packageName' => sprintf('%s/%s', fake()->slug(), fake()->slug()),
    ]);

    expect($packageVersionsTable->render()->name())
        ->toBe('compass::components.package-versions-table');
});

test('url is the compass page of the package', function (): void {
    $packageName = sprintf('%s/%s', fake()->slug(), fake()->slug());

    $packageVersionsTable = $this->app->make(PackageVersionsTable::class, [
        'packageName' => $packageName,
    ]);

    expect($packageVersionsTable->url)
        ->toBe(route(RouteName::PACKAGE, $packageName));
});

test('version counts are the versions of the package locked by the projects', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);
    $lockedPackage = ComposerLockPackage::factory()->create([
        'composer_lock_id' => ComposerLock::factory()->create(['repository_id' => $repository->id])->id,
    ]);

    $packageVersionsTable = $this->app->make(PackageVersionsTable::class, [
        'packageName' => $lockedPackage->name,
    ]);

    expect($packageVersionsTable->versionCounts->all())
        ->toEqual([new PackageVersionCount($lockedPackage->version, 1)]);
});
