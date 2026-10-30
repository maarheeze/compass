<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlSynchronizer;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;

test('store stores the dependabot yml of the repository', function (): void {
    $repository = Repository::factory()->create();
    $oid = fake()->sha1();
    $packageEcosystems = new Collection(fake()->randomElements(['composer', 'docker', 'github-actions', 'npm'], 2));

    $dependabotYmlSynchronizer = $this->app->make(DependabotYmlSynchronizer::class);
    $dependabotYmlSynchronizer->store($repository, $oid, $packageEcosystems);

    $this->assertDatabaseHas(DependabotYml::class, [
        'repository_id' => $repository->id,
        'oid' => $oid,
        'package_ecosystems' => $packageEcosystems->toJson(),
    ]);
});

test('store replaces the dependabot yml the repository already has', function (): void {
    $dependabotYml = DependabotYml::factory()->create();
    $oid = fake()->unique()->sha1();

    $dependabotYmlSynchronizer = $this->app->make(DependabotYmlSynchronizer::class);
    $dependabotYmlSynchronizer->store($dependabotYml->repository, $oid, new Collection());

    $this->assertDatabaseHas(DependabotYml::class, [
        'id' => $dependabotYml->id,
        'oid' => $oid,
    ]);
});

test('delete removes the dependabot yml of the repository', function (): void {
    $dependabotYml = DependabotYml::factory()->create();

    $dependabotYmlSynchronizer = $this->app->make(DependabotYmlSynchronizer::class);
    $dependabotYmlSynchronizer->delete($dependabotYml->repository);

    $this->assertDatabaseMissing(DependabotYml::class, ['id' => $dependabotYml->id]);
});
