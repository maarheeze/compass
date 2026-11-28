<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorSchedule;
use Maarheeze\Compass\Collector\Drivers\CompassClient\CompassClientDriver;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Collector\SiteReports\CollectedScheduledTask;
use Maarheeze\Compass\Collector\SiteReports\CollectedScheduler;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Random\Randomizer;

test('site report holds what the client reported', function (): void {
    $appDebug = fake()->boolean();
    $appEnv = fake()->word();
    $appName = fake()->company();
    $maintenanceMode = fake()->boolean();
    $path = sprintf('/%s', fake()->slug());
    $frequencyMinutes = fake()->numberBetween(1, 60);
    $queueDriver = fake()->slug();
    $command = sprintf('php artisan %s', fake()->slug());
    $expression = sprintf('%d * * * *', fake()->numberBetween(0, 59));
    $schedulerLastRanAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => $appDebug,
        'app_env' => $appEnv,
        'app_name' => $appName,
        'maintenance_mode' => $maintenanceMode,
        'endpoints' => [
            [
                'path' => $path,
                'frequency_minutes' => $frequencyMinutes,
            ],
        ],
        'scheduler' => [
            'last_ran_at' => $schedulerLastRanAt->toIso8601String(),
            'tasks' => [
                [
                    'command' => $command,
                    'expression' => $expression,
                ],
            ],
        ],
        'queue_driver' => $queueDriver,
    ]),
    ]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $collectedSiteReport = $compassClientDriver->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::installed(
            $appDebug,
            $appEnv,
            $appName,
            [new CollectedSiteEndpoint(new EndpointPath($path), $frequencyMinutes)],
            $maintenanceMode,
            $queueDriver,
            new CollectedScheduler($schedulerLastRanAt, [new CollectedScheduledTask($command, $expression)]),
        ));
});

test('site report is incompatible when the client reports a scheduler without its tasks', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'scheduler' => [
            'last_ran_at' => null,
        ],
    ]),
    ]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $collectedSiteReport = $compassClientDriver->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::incompatible());
});

test('site report is not installed when the site has no client', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $collectedSiteReport = $compassClientDriver->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::notInstalled());
});

test('site report is unauthorised when the client refuses the key', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 401)]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $collectedSiteReport = $compassClientDriver->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::unauthorised());
});

test('site report throws collecting failed when the site cannot be reached', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::failedConnection()]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $compassClientDriver->siteReport(SiteReport::factory()->create());
})->throws(CollectingFailed::class);

test('site report asks the site with its key', function (): void {
    $site = Site::factory()->create(['key' => fake()->sha1()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $compassClientDriver->siteReport(SiteReport::factory()->for($site)->create());

    $http->assertSent(static function (HttpRequest $request) use ($site): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $site->key));
    });
});

test('site report gives a site without a key a new one', function (): void {
    $site = Site::factory()->create(['key' => null]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $compassClientDriver->siteReport(SiteReport::factory()->for($site)->create());

    expect($site->refresh()->key)
        ->toMatch('/\A[0-9a-f]{64}\z/');
});

test('site report asks a site without a key with the key it gave it', function (): void {
    $site = Site::factory()->create(['key' => null]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientDriver = new CompassClientDriver(
        new Randomizer(),
        new SiteClient(
            $this->app->make(CollectorSchedule::class),
            $http,
            sprintf('/%s', fake()->slug()),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
    );
    $compassClientDriver->siteReport(SiteReport::factory()->for($site)->create());

    $http->assertSent(static function (HttpRequest $request) use ($site): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $site->refresh()->key));
    });
});
