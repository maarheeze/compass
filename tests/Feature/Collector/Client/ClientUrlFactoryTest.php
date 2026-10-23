<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\ClientUrlFactory;
use Maarheeze\Compass\Models\Site;

test('create appends the default path to the url of a site without a path of its own', function (): void {
    $defaultPath = sprintf('/%s', fake()->slug());
    $this->setConfiguration('compass.client.path', $defaultPath);
    $site = Site::factory()->make(['client_path' => null]);

    $clientUrlFactory = $this->app->make(ClientUrlFactory::class);
    $clientUrl = $clientUrlFactory->create($site);

    expect($clientUrl)
        ->toBe(sprintf('%s%s', rtrim($site->url, '/'), $defaultPath));
});

test('create appends the path of the site', function (): void {
    $site = Site::factory()->make(['client_path' => sprintf('/%s', fake()->slug())]);

    $clientUrlFactory = $this->app->make(ClientUrlFactory::class);
    $clientUrl = $clientUrlFactory->create($site);

    expect($clientUrl)
        ->toBe(sprintf('%s%s', rtrim($site->url, '/'), $site->client_path));
});

test('create joins a url ending in a slash with one slash', function (): void {
    $host = fake()->domainName();
    $clientPath = fake()->slug();
    $site = Site::factory()->make([
        'url' => sprintf('https://%s/', $host),
        'client_path' => sprintf('/%s', $clientPath),
    ]);

    $clientUrlFactory = $this->app->make(ClientUrlFactory::class);
    $clientUrl = $clientUrlFactory->create($site);

    expect($clientUrl)
        ->toBe(sprintf('https://%s/%s', $host, $clientPath));
});
