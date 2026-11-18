<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlSynchroniser;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;

test('store stores the dependabot yml of the repository', function (): void {
    $repository = Repository::factory()->create();
    $version = fake()->sha1();
    $packageEcosystems = new Collection(fake()->randomElements(['composer', 'docker', 'github-actions', 'npm'], 2));

    $dependabotYmlSynchroniser = $this->app->make(DependabotYmlSynchroniser::class);
    $dependabotYmlSynchroniser->store($repository, $version, $packageEcosystems);

    $this->assertDatabaseHas(DependabotYml::class, [
        'repository_id' => $repository->id,
        'version' => $version,
        'package_ecosystems' => $packageEcosystems->toJson(),
    ]);
});

test('store replaces the dependabot yml the repository already has', function (): void {
    $dependabotYml = DependabotYml::factory()->create();
    $version = fake()->unique()->sha1();

    $dependabotYmlSynchroniser = $this->app->make(DependabotYmlSynchroniser::class);
    $dependabotYmlSynchroniser->store($dependabotYml->repository, $version, new Collection());

    $this->assertDatabaseHas(DependabotYml::class, [
        'id' => $dependabotYml->id,
        'version' => $version,
    ]);
});

test('delete removes the dependabot yml of the repository', function (): void {
    $dependabotYml = DependabotYml::factory()->create();

    $dependabotYmlSynchroniser = $this->app->make(DependabotYmlSynchroniser::class);
    $dependabotYmlSynchroniser->delete($dependabotYml->repository);

    $this->assertDatabaseMissing(DependabotYml::class, ['id' => $dependabotYml->id]);
});
