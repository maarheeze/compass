<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\Repository;
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

test('of source keeps a site of the source', function (): void {
    $siteOfSource = Site::factory()->create();

    $sitesOfSource = Site::query()
        ->ofSource($siteOfSource->source)
        ->get();

    expect($sitesOfSource->sole()->is($siteOfSource))
        ->toBeTrue();
});

test('of source leaves out a site of another source', function (): void {
    Site::factory()->create(['source' => fake()->unique()->slug()]);

    $sitesOfSource = Site::query()
        ->ofSource(fake()->unique()->slug())
        ->get();

    expect($sitesOfSource)
        ->toBeEmpty();
});

test('of repository source keeps a site whose repository belongs to the source', function (): void {
    $repository = Repository::factory()->create(['source_url' => fake()->url()]);
    $siteOfRepositorySource = Site::factory()->create(['repository_url' => $repository->source_url]);

    $sitesOfRepositorySource = Site::query()
        ->ofRepositorySource($repository->source)
        ->get();

    expect($sitesOfRepositorySource->sole()->is($siteOfRepositorySource))
        ->toBeTrue();
});

test('of repository source leaves out a site whose repository belongs to another source', function (): void {
    $repository = Repository::factory()->create([
        'source_url' => fake()->url(),
        'source' => fake()->unique()->slug(),
    ]);
    Site::factory()->create(['repository_url' => $repository->source_url]);

    $sitesOfRepositorySource = Site::query()
        ->ofRepositorySource(fake()->unique()->slug())
        ->get();

    expect($sitesOfRepositorySource)
        ->toBeEmpty();
});

test('listed is the sites in the order of their name', function (): void {
    $bravoSite = Site::factory()->create(['name' => 'bravo']);
    $alphaSite = Site::factory()->create(['name' => 'alpha']);

    $listedSites = Site::query()->listed();

    expect($listedSites->modelKeys())
        ->toEqual([$alphaSite->getKey(), $bravoSite->getKey()]);
});

test('of php version keeps a site on the php version', function (): void {
    $site = Site::factory()->create(['php_version' => 'php84']);

    $sitesOfPhpVersion = Site::query()
        ->ofPhpVersion('php84')
        ->get();

    expect($sitesOfPhpVersion->sole()->is($site))
        ->toBeTrue();
});

test('of php version leaves out a site on another php version', function (): void {
    Site::factory()->create(['php_version' => 'php83']);

    $sitesOfPhpVersion = Site::query()
        ->ofPhpVersion('php84')
        ->get();

    expect($sitesOfPhpVersion)
        ->toBeEmpty();
});

test('of server keeps a site on the server', function (): void {
    $site = Site::factory()->create();

    $sitesOfServer = Site::query()
        ->ofServer($site->server_name)
        ->get();

    expect($sitesOfServer->sole()->is($site))
        ->toBeTrue();
});

test('of server leaves out a site on another server', function (): void {
    Site::factory()->create(['server_name' => fake()->unique()->slug()]);

    $sitesOfServer = Site::query()
        ->ofServer(fake()->unique()->slug())
        ->get();

    expect($sitesOfServer)
        ->toBeEmpty();
});

test('listed page is the sites in the order of their name', function (): void {
    $bravoSite = Site::factory()->create(['name' => 'bravo']);
    $alphaSite = Site::factory()->create(['name' => 'alpha']);

    $listedSitesPage = Site::query()->listedPage(new ItemsPerPage(fake()->numberBetween(2, 100)));

    expect($listedSitesPage->getCollection()->modelKeys())
        ->toEqual([$alphaSite->getKey(), $bravoSite->getKey()]);
});

test('listed page has the given number of sites per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);

    $listedSitesPage = Site::query()->listedPage(new ItemsPerPage($itemsPerPage));

    expect($listedSitesPage->perPage())
        ->toBe($itemsPerPage);
});
