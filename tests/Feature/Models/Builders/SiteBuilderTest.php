<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

test('collected keeps a collected site', function (): void {
    $collectedSite = Site::factory()->create(['missing_at' => null]);

    $collectedSites = Site::query()
        ->collected()
        ->get();

    expect($collectedSites->sole()->is($collectedSite))
        ->toBeTrue();
});

test('collected leaves out a missing site', function (): void {
    Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $collectedSites = Site::query()
        ->collected()
        ->get();

    expect($collectedSites)
        ->toBeEmpty();
});

test('missing keeps a missing site that is not ignored', function (): void {
    $missingSite = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $missingSites = Site::query()
        ->missing()
        ->get();

    expect($missingSites->sole()->is($missingSite))
        ->toBeTrue();
});

test('missing leaves out an ignored site', function (): void {
    Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingSites = Site::query()
        ->missing()
        ->get();

    expect($missingSites)
        ->toBeEmpty();
});

test('missing leaves out a collected site', function (): void {
    Site::factory()->create(['missing_at' => null]);

    $missingSites = Site::query()
        ->missing()
        ->get();

    expect($missingSites)
        ->toBeEmpty();
});

test('not collected keeps a missing site', function (): void {
    $notCollectedSite = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $notCollectedSites = Site::query()
        ->notCollected()
        ->get();

    expect($notCollectedSites->sole()->is($notCollectedSite))
        ->toBeTrue();
});

test('not collected keeps an ignored site', function (): void {
    $notCollectedSite = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $notCollectedSites = Site::query()
        ->notCollected()
        ->get();

    expect($notCollectedSites->sole()->is($notCollectedSite))
        ->toBeTrue();
});

test('not collected leaves out a collected site', function (): void {
    Site::factory()->create(['missing_at' => null]);

    $notCollectedSites = Site::query()
        ->notCollected()
        ->get();

    expect($notCollectedSites)
        ->toBeEmpty();
});

test('not returned by run keeps a site returned by another run', function (): void {
    $site = Site::factory()->create();

    $sitesNotReturnedByRun = Site::query()
        ->notReturnedByRun(Uuid::generate())
        ->get();

    expect($sitesNotReturnedByRun->sole()->is($site))
        ->toBeTrue();
});

test('not returned by run leaves out a site returned by the run', function (): void {
    $site = Site::factory()->create();

    $sitesNotReturnedByRun = Site::query()
        ->notReturnedByRun($site->run_id)
        ->get();

    expect($sitesNotReturnedByRun)
        ->toBeEmpty();
});
