<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\RequiredPackageCollection;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSummary;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSynchronizer;
use Maarheeze\Compass\Collector\Composer\Json\RequiredPackage;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\Repository;

test('store stores the composer json of the repository', function (): void {
    $repository = Repository::factory()->create();
    $oid = fake()->sha1();
    $composerJsonSummary = new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        new RequiredPackageCollection(),
    );

    $composerJsonSynchronizer = $this->app->make(ComposerJsonSynchronizer::class);
    $composerJsonSynchronizer->store($repository, $oid, $composerJsonSummary);

    $this->assertDatabaseHas(ComposerJson::class, [
        'repository_id' => $repository->id,
        'oid' => $oid,
        'type' => $composerJsonSummary->type,
        'php_constraint' => $composerJsonSummary->phpConstraint,
    ]);
});

test('store stores the required packages', function (): void {
    $repository = Repository::factory()->create();
    $requiredPackage = new RequiredPackage(
        sprintf('%s/%s', fake()->slug(), fake()->slug()),
        fake()->numerify('^#.#'),
        fake()->randomElement(PackageSection::cases()),
    );

    $composerJsonSynchronizer = $this->app->make(ComposerJsonSynchronizer::class);
    $composerJsonSynchronizer->store($repository, fake()->sha1(), new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        new RequiredPackageCollection([$requiredPackage]),
    ));

    $this->assertDatabaseHas(ComposerJsonPackage::class, [
        'composer_json_id' => ComposerJson::query()->where('repository_id', $repository->id)->sole()->id,
        'name' => $requiredPackage->name,
        'constraint' => $requiredPackage->constraint,
        'section' => $requiredPackage->section,
    ]);
});

test('store replaces the composer json collected before', function (): void {
    $composerJson = ComposerJson::factory()->create(['oid' => fake()->unique()->sha1()]);
    $oid = fake()->unique()->sha1();

    $composerJsonSynchronizer = $this->app->make(ComposerJsonSynchronizer::class);
    $composerJsonSynchronizer->store($composerJson->repository, $oid, new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        new RequiredPackageCollection(),
    ));

    $this->assertDatabaseHas(ComposerJson::class, [
        'id' => $composerJson->id,
        'oid' => $oid,
    ]);
});

test('store removes the packages collected before', function (): void {
    $composerJsonPackage = ComposerJsonPackage::factory()->create();
    $repository = $composerJsonPackage->composerJson->repository;

    $composerJsonSynchronizer = $this->app->make(ComposerJsonSynchronizer::class);
    $composerJsonSynchronizer->store($repository, fake()->sha1(), new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        new RequiredPackageCollection(),
    ));

    $this->assertDatabaseMissing(ComposerJsonPackage::class, ['id' => $composerJsonPackage->id]);
});

test('delete removes the composer json of the repository', function (): void {
    $composerJson = ComposerJson::factory()->create();

    $composerJsonSynchronizer = $this->app->make(ComposerJsonSynchronizer::class);
    $composerJsonSynchronizer->delete($composerJson->repository);

    $this->assertDatabaseMissing(ComposerJson::class, ['id' => $composerJson->id]);
});
