<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSites;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

test('handle adds the inspection of a collected site to its batch', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    $site = Site::factory()->create();

    [$inspectSites, $batchFake] = new InspectSites($source)->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    $addedSiteIds = array_map(
        static function (InspectSite $inspectSite): Uuid {
            return $inspectSite->site->id;
        },
        $batchFake->added,
    );

    expect($addedSiteIds)
        ->toEqual([$site->id]);
});

test('handle hands its source to the inspection of a site', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    Site::factory()->create();

    [$inspectSites, $batchFake] = new InspectSites($source)->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    $addedSources = array_map(
        static function (InspectSite $inspectSite): string {
            return $inspectSite->source;
        },
        $batchFake->added,
    );

    expect($addedSources)
        ->toBe([$source]);
});

test('handle leaves out a missing site', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    Site::factory()->create(['missing_at' => fake()->dateTime()]);

    [$inspectSites, $batchFake] = new InspectSites($source)->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle refuses a source without a site reports collector', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => fake()->slug(),
        ],
    ]);
    Site::factory()->create();

    $this->expectException(InvalidArgumentException::class);

    [$inspectSites] = new InspectSites($source)->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);
});

test('lock key is the source', function (): void {
    $source = fake()->slug();

    $inspectSites = new InspectSites($source);
    $lockKey = $inspectSites->lockKey();

    expect($lockKey)
        ->toBe($source);
});
