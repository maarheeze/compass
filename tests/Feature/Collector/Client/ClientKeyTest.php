<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Models\Site;

test('for site is the key of the site', function (): void {
    $site = Site::factory()->make(['client_key' => fake()->unique()->sha1()]);

    $clientKey = new ClientKey(fake()->unique()->sha1());
    $keyForSite = $clientKey->forSite($site);

    expect($keyForSite)
        ->toBe($site->client_key);
});

test('for site is the default key for a site without a key of its own', function (?string $siteKey): void {
    $defaultKey = fake()->sha1();

    $clientKey = new ClientKey($defaultKey);
    $keyForSite = $clientKey->forSite(Site::factory()->make(['client_key' => $siteKey]));

    expect($keyForSite)
        ->toBe($defaultKey);
})->with([
    'without a key' => [null],
    'with an empty key' => [''],
]);
