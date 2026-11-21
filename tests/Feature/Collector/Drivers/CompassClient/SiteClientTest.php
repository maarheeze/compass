<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\CollectorSchedule;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Drivers\CompassClient\ClientUnreachable;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Console\CollectCommand;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('post requests the path of the site report from its site', function (): void {
    $siteReport = SiteReport::factory()->create(['path' => sprintf('/%s', fake()->slug())]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient(
        $this->app->make(CollectorSchedule::class),
        $http,
        sprintf('/%s', fake()->slug()),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post($siteReport, fake()->sha1());

    $http->assertSent(static function (HttpRequest $request) use ($siteReport): bool {
        return $request->url() === sprintf('%s%s', rtrim($siteReport->site->url, '/'), $siteReport->path);
    });
});

test('post requests the path of the source for a site report without one', function (): void {
    $siteReport = SiteReport::factory()->create(['path' => null]);
    $path = sprintf('/%s', fake()->slug());
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient(
        $this->app->make(CollectorSchedule::class),
        $http,
        $path,
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post($siteReport, fake()->sha1());

    $http->assertSent(static function (HttpRequest $request) use ($path, $siteReport): bool {
        return $request->url() === sprintf('%s%s', rtrim($siteReport->site->url, '/'), $path);
    });
});

test('post joins a site url ending in a slash with one slash', function (): void {
    $host = fake()->domainName();
    $clientPath = fake()->slug();
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->state(['url' => sprintf('https://%s/', $host)]))
        ->create(['path' => sprintf('/%s', $clientPath)]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient(
        $this->app->make(CollectorSchedule::class),
        $http,
        sprintf('/%s', fake()->slug()),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post($siteReport, fake()->sha1());

    $http->assertSent(static function (HttpRequest $request) use ($clientPath, $host): bool {
        return $request->url() === sprintf('https://%s/%s', $host, $clientPath);
    });
});

test('post sends the key', function (): void {
    $key = fake()->sha1();
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient(
        $this->app->make(CollectorSchedule::class),
        $http,
        sprintf('/%s', fake()->slug()),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post(SiteReport::factory()->create(), $key);

    $http->assertSent(static function (HttpRequest $request) use ($key): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $key));
    });
});

test('post throws when the site cannot be reached', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::failedConnection()]);

    $siteClient = new SiteClient(
        $this->app->make(CollectorSchedule::class),
        $http,
        sprintf('/%s', fake()->slug()),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post(SiteReport::factory()->create(), fake()->sha1());
})->throws(ClientUnreachable::class);

test('post sends the url of the compass api when the site reports collector of its source is scheduled', function (): void {
    $source = fake()->slug();
    $schedule = new Schedule();
    $schedule->command(CollectCommand::SIGNATURE, [$source, CollectorType::SITE_REPORTS->value]);
    $this->app->instance(Schedule::class, $schedule);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient(
        $this->app->make(CollectorSchedule::class),
        $http,
        sprintf('/%s', fake()->slug()),
        $source,
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post(SiteReport::factory()->create(), fake()->sha1());

    $http->assertSent(static function (HttpRequest $request): bool {
        return $request['compass_api_url'] === route(RouteName::API_CLIENT);
    });
});

test('post sends the url of the compass api as valid until an hour after the next run of the site reports collector', function (): void {
    $this->app->instance(ClockInterface::class, new MockClock(CarbonImmutable::instance(fake()->dateTime())));
    $source = fake()->slug();
    $schedule = new Schedule();
    $schedule
        ->command(CollectCommand::SIGNATURE, [$source, CollectorType::SITE_REPORTS->value])
        ->hourly();
    $this->app->instance(Schedule::class, $schedule);
    $collectorSchedule = $this->app->make(CollectorSchedule::class);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient(
        $collectorSchedule,
        $http,
        sprintf('/%s', fake()->slug()),
        $source,
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post(SiteReport::factory()->create(), fake()->sha1());

    $http->assertSent(static function (HttpRequest $request) use ($collectorSchedule, $source): bool {
        $siteReportsNextRunAt = $collectorSchedule->nextRunAt($source, CollectorType::SITE_REPORTS);

        return $request['compass_api_url_valid_until'] === $siteReportsNextRunAt?->addHour()->toIso8601String();
    });
});

test('post leaves out the compass api when the site reports collector of its source is not scheduled', function (): void {
    $this->app->instance(Schedule::class, new Schedule());
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient(
        $this->app->make(CollectorSchedule::class),
        $http,
        sprintf('/%s', fake()->slug()),
        fake()->slug(),
        $this->app->make(UrlGenerator::class),
    );
    $siteClient->post(SiteReport::factory()->create(), fake()->sha1());

    $http->assertSent(static function (HttpRequest $request): bool {
        return $request->data() === [];
    });
});
