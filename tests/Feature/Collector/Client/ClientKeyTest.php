<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Models\Site;

test('find is the key of the site', function (): void {
    $site = Site::factory()->make(['client_key' => fake()->unique()->sha1()]);
    $this->setConfiguration('compass.client.key', fake()->unique()->sha1());

    $clientKey = $this->app->make(ClientKey::class);
    $foundKey = $clientKey->find($site);

    expect($foundKey)
        ->toBe($site->client_key);
});

test('find is the default key for a site without a key of its own', function (): void {
    $defaultKey = fake()->sha1();
    $this->setConfiguration('compass.client.key', $defaultKey);

    $clientKey = $this->app->make(ClientKey::class);
    $foundKey = $clientKey->find(Site::factory()->make(['client_key' => null]));

    expect($foundKey)
        ->toBe($defaultKey);
});

test('find is null without a key of the site and without a default key', function (): void {
    $this->setConfiguration('compass.client.key', null);

    $clientKey = $this->app->make(ClientKey::class);
    $foundKey = $clientKey->find(Site::factory()->make(['client_key' => null]));

    expect($foundKey)
        ->toBeNull();
});

test('find treats an empty default key as no key', function (): void {
    $this->setConfiguration('compass.client.key', '');

    $clientKey = $this->app->make(ClientKey::class);
    $foundKey = $clientKey->find(Site::factory()->make(['client_key' => null]));

    expect($foundKey)
        ->toBeNull();
});

test('has default key with a default key', function (): void {
    $this->setConfiguration('compass.client.key', fake()->sha1());

    $clientKey = $this->app->make(ClientKey::class);
    $hasDefaultKey = $clientKey->hasDefaultKey();

    expect($hasDefaultKey)
        ->toBeTrue();
});

test('has default key is false without a default key', function (): void {
    $this->setConfiguration('compass.client.key', null);

    $clientKey = $this->app->make(ClientKey::class);
    $hasDefaultKey = $clientKey->hasDefaultKey();

    expect($hasDefaultKey)
        ->toBeFalse();
});
