<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedDependabotYmls;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectDependabotYml;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('handle adds collect dependabot yml when its oid differs from the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_oid' => fake()->unique()->sha1(),
    ]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'oid' => fake()->unique()->sha1(),
    ]);

    [$collectChangedDependabotYmls, $batchFake] = new CollectChangedDependabotYmls(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectDependabotYml $collectDependabotYml): Uuid {
            return $collectDependabotYml->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toEqual([$repository->id]);
});

test('handle adds collect dependabot yml when the dependabot yml is gone', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_oid' => null,
    ]);
    DependabotYml::factory()->create(['repository_id' => $repository->id]);

    [$collectChangedDependabotYmls, $batchFake] = new CollectChangedDependabotYmls(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectDependabotYml $collectDependabotYml): Uuid {
            return $collectDependabotYml->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toEqual([$repository->id]);
});

test('handle does not add collect dependabot yml when its oid is the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_oid' => fake()->sha1(),
    ]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'oid' => $repository->dependabot_yml_oid,
    ]);

    [$collectChangedDependabotYmls, $batchFake] = new CollectChangedDependabotYmls(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle does not add collect dependabot yml for a repository without one', function (): void {
    $runId = Uuid::generate();
    Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_oid' => null,
    ]);

    [$collectChangedDependabotYmls, $batchFake] = new CollectChangedDependabotYmls(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle ignores repositories of another run', function (): void {
    Repository::factory()->create([
        'run_id' => Uuid::generate(),
        'dependabot_yml_oid' => fake()->sha1(),
    ]);

    [$collectChangedDependabotYmls, $batchFake] = new CollectChangedDependabotYmls(
        Uuid::generate(),
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle adds collect dependabot yml for the source of its run', function (): void {
    $runId = Uuid::generate();
    $sourceKey = fake()->slug();
    Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_oid' => fake()->sha1(),
    ]);

    [$collectChangedDependabotYmls, $batchFake] = new CollectChangedDependabotYmls($runId, $sourceKey)->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    expect($batchFake->added[0]->sourceKey)
        ->toBe($sourceKey);
});
