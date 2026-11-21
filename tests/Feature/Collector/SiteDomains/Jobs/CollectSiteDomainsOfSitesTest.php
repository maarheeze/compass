<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteDomains\Jobs\CollectSiteDomains;
use Maarheeze\Compass\Collector\SiteDomains\Jobs\CollectSiteDomainsOfSites;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

test('handle adds the collection of the domains of a site returned by the run to its batch', function (): void {
    $runId = Uuid::generate();
    $site = Site::factory()->create(['run_id' => $runId]);

    [$collectSiteDomainsOfSites, $batchFake] = new CollectSiteDomainsOfSites($runId, $site->source)->withFakeBatch();
    $this->app->call([$collectSiteDomainsOfSites, 'handle']);

    $addedSiteIds = array_map(
        static function (CollectSiteDomains $collectSiteDomains): Uuid {
            return $collectSiteDomains->site->id;
        },
        $batchFake->added,
    );

    expect($addedSiteIds)
        ->toEqual([$site->id]);
});

test('handle leaves out a site of another run', function (): void {
    $site = Site::factory()->create(['run_id' => Uuid::generate()]);

    [$collectSiteDomainsOfSites, $batchFake] = new CollectSiteDomainsOfSites(Uuid::generate(), $site->source)->withFakeBatch();
    $this->app->call([$collectSiteDomainsOfSites, 'handle']);

    expect($batchFake->added)
        ->toBe([]);
});

test('handle leaves out a site of another source', function (): void {
    $runId = Uuid::generate();
    Site::factory()->create(['run_id' => $runId, 'source' => fake()->unique()->slug()]);

    [$collectSiteDomainsOfSites, $batchFake] = new CollectSiteDomainsOfSites($runId, fake()->unique()->slug())->withFakeBatch();
    $this->app->call([$collectSiteDomainsOfSites, 'handle']);

    expect($batchFake->added)
        ->toBe([]);
});

test('lock key is the source', function (): void {
    $source = fake()->slug();

    $collectSiteDomainsOfSites = new CollectSiteDomainsOfSites(Uuid::generate(), $source);
    $lockKey = $collectSiteDomainsOfSites->lockKey();

    expect($lockKey)
        ->toBe($source);
});
