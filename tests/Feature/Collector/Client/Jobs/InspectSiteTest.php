<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('handle stores the report the collector of its source collected', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $inspectSite = new InspectSite($site, $sourceKey);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(SiteReport::class, [
        'site_id' => $site->id,
        'source' => $sourceKey,
        'status' => SiteReportStatus::INSTALLED,
    ]);
});

test('handle asks the site with the key of its report for its source', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'compass-client',
            'key' => fake()->unique()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    $siteReport = SiteReport::factory()->create([
        'source' => $sourceKey,
        'key' => fake()->unique()->sha1(),
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($siteReport->site, $sourceKey);
    $this->app->call([$inspectSite, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($siteReport): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $siteReport->key));
    });
});

test('handle fails when the site cannot be reached', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $this->expectException(CollectingFailed::class);

    $inspectSite = new InspectSite(Site::factory()->create(), $sourceKey);
    $this->app->call([$inspectSite, 'handle']);
});

test('handle leaves a missing site alone', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $inspectSite = new InspectSite($site, $sourceKey);
    $this->app->call([$inspectSite, 'handle']);

    $http->assertNothingSent();
});

test('handle adds the site to the log context', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($site, $sourceKey);
    $this->app->call([$inspectSite, 'handle']);

    expect($this->app->make(Context::class)->get('site'))
        ->toBe($site->name);
});

test('handle raises site inspected with the site', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($site, $sourceKey);
    $this->app->call([$inspectSite, 'handle']);

    $eventFake->assertDispatched(SiteInspected::class, static function (SiteInspected $siteInspected) use ($site): bool {
        return $siteInspected->site->is($site);
    });
});

test('handle refuses a source without a site reports collector', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => fake()->slug(),
        ],
    ]);

    $this->expectException(InvalidArgumentException::class);

    $inspectSite = new InspectSite(Site::factory()->create(), $sourceKey);
    $this->app->call([$inspectSite, 'handle']);
});
