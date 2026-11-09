<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Dependabot\Jobs\CollectChangedDependabotYmls;
use Maarheeze\Compass\Collector\Dependabot\Jobs\CollectDependabotYml;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('handle adds collect dependabot yml when its version differs from the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_version' => fake()->unique()->sha1(),
    ]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'version' => fake()->unique()->sha1(),
    ]);

    $collectChangedDependabotYmls = new CollectChangedDependabotYmls($runId, fake()->slug());
    [$collectChangedDependabotYmls, $batchFake] = $collectChangedDependabotYmls->withFakeBatch();
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
        'dependabot_yml_version' => null,
    ]);
    DependabotYml::factory()->create(['repository_id' => $repository->id]);

    $collectChangedDependabotYmls = new CollectChangedDependabotYmls($runId, fake()->slug());
    [$collectChangedDependabotYmls, $batchFake] = $collectChangedDependabotYmls->withFakeBatch();
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

test('handle does not add collect dependabot yml when its version is the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_version' => fake()->sha1(),
    ]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'version' => $repository->dependabot_yml_version,
    ]);

    $collectChangedDependabotYmls = new CollectChangedDependabotYmls($runId, fake()->slug());
    [$collectChangedDependabotYmls, $batchFake] = $collectChangedDependabotYmls->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle does not add collect dependabot yml for a repository without one', function (): void {
    $runId = Uuid::generate();
    Repository::factory()->create([
        'run_id' => $runId,
        'dependabot_yml_version' => null,
    ]);

    $collectChangedDependabotYmls = new CollectChangedDependabotYmls($runId, fake()->slug());
    [$collectChangedDependabotYmls, $batchFake] = $collectChangedDependabotYmls->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle ignores repositories of another run', function (): void {
    Repository::factory()->create([
        'run_id' => Uuid::generate(),
        'dependabot_yml_version' => fake()->sha1(),
    ]);

    $collectChangedDependabotYmls = new CollectChangedDependabotYmls(Uuid::generate(), fake()->slug());
    [$collectChangedDependabotYmls, $batchFake] = $collectChangedDependabotYmls->withFakeBatch();
    $this->app->call([$collectChangedDependabotYmls, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('lock key is the source', function (): void {
    $source = fake()->slug();

    $collectChangedDependabotYmls = new CollectChangedDependabotYmls(Uuid::generate(), $source);
    $lockKey = $collectChangedDependabotYmls->lockKey();

    expect($lockKey)
        ->toBe($source);
});
