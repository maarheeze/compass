<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSummary;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSynchroniser;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\Repository;

test('store stores the composer json of the repository', function (): void {
    $repository = Repository::factory()->create();
    $version = fake()->sha1();
    $composerJsonSummary = new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        fake()->optional()->word(),
        fake()->boolean(),
        new ComposerJsonPackageCollection(),
    );

    $composerJsonSynchroniser = $this->app->make(ComposerJsonSynchroniser::class);
    $composerJsonSynchroniser->store($repository, $version, $composerJsonSummary);

    $this->assertDatabaseHas(ComposerJson::class, [
        'repository_id' => $repository->id,
        'version' => $version,
        'type' => $composerJsonSummary->type,
        'php_constraint' => $composerJsonSummary->phpConstraint,
        'minimum_stability' => $composerJsonSummary->minimumStability,
        'prefer_stable' => $composerJsonSummary->preferStable,
    ]);
});

test('store stores the required packages', function (): void {
    $repository = Repository::factory()->create();
    $requiredPackage = new ComposerJsonPackage([
        'name' => sprintf('%s/%s', fake()->slug(), fake()->slug()),
        'constraint' => fake()->numerify('^#.#'),
        'section' => fake()->randomElement(PackageSection::cases()),
    ]);

    $composerJsonSynchroniser = $this->app->make(ComposerJsonSynchroniser::class);
    $composerJsonSynchroniser->store($repository, fake()->sha1(), new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        fake()->optional()->word(),
        fake()->boolean(),
        new ComposerJsonPackageCollection([$requiredPackage]),
    ));

    $this->assertDatabaseHas(ComposerJsonPackage::class, [
        'composer_json_id' => ComposerJson::query()->where('repository_id', $repository->id)->sole()->id,
        'name' => $requiredPackage->name,
        'constraint' => $requiredPackage->constraint,
        'section' => $requiredPackage->section,
    ]);
});

test('store replaces the composer json collected before', function (): void {
    $composerJson = ComposerJson::factory()->create(['version' => fake()->unique()->sha1()]);
    $version = fake()->unique()->sha1();

    $composerJsonSynchroniser = $this->app->make(ComposerJsonSynchroniser::class);
    $composerJsonSynchroniser->store($composerJson->repository, $version, new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        fake()->optional()->word(),
        fake()->boolean(),
        new ComposerJsonPackageCollection(),
    ));

    $this->assertDatabaseHas(ComposerJson::class, [
        'id' => $composerJson->id,
        'version' => $version,
    ]);
});

test('store removes the packages collected before', function (): void {
    $composerJsonPackage = ComposerJsonPackage::factory()->create();
    $repository = $composerJsonPackage->composerJson->repository;

    $composerJsonSynchroniser = $this->app->make(ComposerJsonSynchroniser::class);
    $composerJsonSynchroniser->store($repository, fake()->sha1(), new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        fake()->optional()->word(),
        fake()->boolean(),
        new ComposerJsonPackageCollection(),
    ));

    $this->assertDatabaseMissing(ComposerJsonPackage::class, ['id' => $composerJsonPackage->id]);
});

test('delete removes the composer json of the repository', function (): void {
    $composerJson = ComposerJson::factory()->create();

    $composerJsonSynchroniser = $this->app->make(ComposerJsonSynchroniser::class);
    $composerJsonSynchroniser->delete($composerJson->repository);

    $this->assertDatabaseMissing(ComposerJson::class, ['id' => $composerJson->id]);
});
