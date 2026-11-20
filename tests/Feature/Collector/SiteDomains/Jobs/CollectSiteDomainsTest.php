<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\SiteDomains\Jobs\CollectSiteDomains;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteDomain;

test('handle stores the domains forge lists for the site', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $site = Site::factory()->create(['source' => $source]);
    $name = fake()->domainName();
    $type = fake()->randomElement(SiteDomainType::cases());
    $status = fake()->randomElement(SiteDomainStatus::cases());
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('####'),
                    'type' => 'domainRecords',
                    'attributes' => [
                        'name' => $name,
                        'type' => $type->value,
                        'status' => $status->value,
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $collectSiteDomains = new CollectSiteDomains($site);
    $this->app->call([$collectSiteDomains, 'handle']);

    $this->assertDatabaseHas(SiteDomain::class, [
        'site_id' => $site->id,
        'name' => $name,
        'type' => $type,
        'status' => $status,
    ]);
});

test('lock key is the site', function (): void {
    $site = Site::factory()->create();

    $collectSiteDomains = new CollectSiteDomains($site);
    $lockKey = $collectSiteDomains->lockKey();

    expect($lockKey)
        ->toBe($site->id->toString());
});
