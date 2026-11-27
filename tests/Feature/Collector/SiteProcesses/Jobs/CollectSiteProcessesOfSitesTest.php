<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteProcesses\Jobs\CollectSiteProcesses;
use Maarheeze\Compass\Collector\SiteProcesses\Jobs\CollectSiteProcessesOfSites;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

test('handle adds the collection of the processes of a collected site of its source to its batch', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'server_pattern' => '/.*/',
        ],
    ]);
    $site = Site::factory()->create(['source' => $source]);

    [$collectSiteProcessesOfSites, $batchFake] = new CollectSiteProcessesOfSites($source)->withFakeBatch();
    $this->app->call([$collectSiteProcessesOfSites, 'handle']);

    $addedSiteIds = array_map(
        static function (CollectSiteProcesses $collectSiteProcesses): Uuid {
            return $collectSiteProcesses->site->id;
        },
        $batchFake->added,
    );

    expect($addedSiteIds)
        ->toEqual([$site->id]);
});

test('handle leaves out a missing site', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'server_pattern' => '/.*/',
        ],
    ]);
    Site::factory()->create([
        'source' => $source,
        'missing_at' => fake()->dateTime(),
    ]);

    [$collectSiteProcessesOfSites, $batchFake] = new CollectSiteProcessesOfSites($source)->withFakeBatch();
    $this->app->call([$collectSiteProcessesOfSites, 'handle']);

    expect($batchFake->added)
        ->toBe([]);
});

test('handle leaves out a site of another source', function (): void {
    $source = fake()->unique()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'server_pattern' => '/.*/',
        ],
    ]);
    Site::factory()->create(['source' => fake()->unique()->slug()]);

    [$collectSiteProcessesOfSites, $batchFake] = new CollectSiteProcessesOfSites($source)->withFakeBatch();
    $this->app->call([$collectSiteProcessesOfSites, 'handle']);

    expect($batchFake->added)
        ->toBe([]);
});

test('handle refuses a source without a site processes collector', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    Site::factory()->create(['source' => $source]);

    [$collectSiteProcessesOfSites] = new CollectSiteProcessesOfSites($source)->withFakeBatch();
    $this->app->call([$collectSiteProcessesOfSites, 'handle']);
})->throws(InvalidArgumentException::class);

test('lock key is the source', function (): void {
    $source = fake()->slug();

    $collectSiteProcessesOfSites = new CollectSiteProcessesOfSites($source);
    $lockKey = $collectSiteProcessesOfSites->lockKey();

    expect($lockKey)
        ->toBe($source);
});
