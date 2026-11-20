<?php

declare(strict_types=1);

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Maarheeze\Compass\Collector\Drivers\CompassClient\ClientIncompatible;
use Maarheeze\Compass\Collector\Drivers\CompassClient\ClientNotInstalled;
use Maarheeze\Compass\Collector\Drivers\CompassClient\ClientUnauthorised;
use Maarheeze\Compass\Collector\Drivers\CompassClient\ClientUnreachable;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteQuery;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;
use Maarheeze\Compass\Console\ScheduledCommands;
use Maarheeze\Compass\Models\SiteReport;

test('report holds what the client reported', function (): void {
    $appDebug = fake()->boolean();
    $appEnv = fake()->word();
    $appName = fake()->company();
    $path = sprintf('/%s', fake()->slug());
    $frequencyMinutes = fake()->numberBetween(1, 60);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => $appDebug,
        'app_env' => $appEnv,
        'app_name' => $appName,
        'endpoints' => [
            [
                'path' => $path,
                'frequency_minutes' => $frequencyMinutes,
            ],
        ],
    ]),
    ]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $clientReport = $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());

    expect($clientReport)
        ->appDebug
        ->toBe($appDebug)
        ->appEnv
        ->toBe($appEnv)
        ->appName
        ->toBe($appName)
        ->endpoints
        ->toEqual([new CollectedSiteEndpoint(new EndpointPath($path), $frequencyMinutes)]);
});

test('report throws when the client reports an endpoint without a frequency', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [
            [
                'path' => sprintf('/%s', fake()->slug()),
            ],
        ],
    ]),
    ]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientIncompatible::class);

test('report throws when the client reports an endpoint as a bare path', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [sprintf('/%s', fake()->slug())],
    ]),
    ]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientIncompatible::class);

test('report throws when the site has no client', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientNotInstalled::class);

test('report throws when the site answers without a site report', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(fake()->randomHtml())]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientNotInstalled::class);

test('report throws when the client reports without a field', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
    ]),
    ]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientIncompatible::class);

test('report throws when the client reports a field of another type', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->word(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientIncompatible::class);

test('report throws when the client refuses the key', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 401)]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientUnauthorised::class);

test('report throws when the client forbids the request', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 403)]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientUnauthorised::class);

test('report throws when the site fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));
    $siteQuery->report(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientUnreachable::class);

test('report puts the error of the response behind the failure of the site', function (): void {
    $status = fake()->numberBetween(500, 599);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, $status)]);
    $siteReport = SiteReport::factory()->create();

    $siteQuery = new SiteQuery(new SiteClient(
        $http,
        sprintf('/%s', fake()->slug()),
        $this->app->make(ScheduledCommands::class),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    ));

    expect(static function () use ($siteQuery, $siteReport) {
        return $siteQuery->report($siteReport, fake()->sha1());
    })
        ->toThrow(static function (ClientUnreachable $clientUnreachable) use ($status): void {
            $requestException = $clientUnreachable->getPrevious();

            expect($requestException)
                ->toBeInstanceOf(RequestException::class)
                ->and($requestException->response->status())
                ->toBe($status);
        });
});
