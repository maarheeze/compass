<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollector;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollector;
use Maarheeze\Compass\Collector\Issues\IssuesCollector;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollector;
use Maarheeze\Compass\Collector\Repositories\RepositoriesCollector;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainsCollector;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollector;
use Maarheeze\Compass\Collector\Sites\SitesCollector;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('a github source collects the repositories of its organisation with its token', function (): void {
    $source = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [],
                    ],
                ],
            ],
        ]),
    ]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, RepositoriesCollector::class)
        ->repositories()
        ->current();

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return $request['variables']['organisation'] === $organisation
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('a github source collects the issues of its organisation with its token', function (): void {
    $source = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, IssuesCollector::class)
        ->issues()
        ->current();

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), 'https://api.github.com/search/issues?')
            && str_starts_with($request['q'], sprintf('org:%s ', $organisation))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('a github source maps the issue types of its config', function (): void {
    $source = fake()->slug();
    $issueTypeName = fake()->word();
    $issueType = fake()->randomElement(IssueType::cases());
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'issue_types' => [$issueTypeName => $issueType],
        ],
    ]);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'incomplete_results' => false,
            'items' => [
                [
                    'node_id' => fake()->sha1(),
                    'repository_url' => fake()->url(),
                    'number' => fake()->numberBetween(1, 9999),
                    'title' => fake()->sentence(),
                    'type' => ['name' => $issueTypeName],
                    'html_url' => fake()->url(),
                    'user' => null,
                    'created_at' => fake()->iso8601(),
                    'assignees' => [],
                ],
            ],
        ]),
    ]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $collectedIssues = $sourceDrivers->collector($source, IssuesCollector::class)
        ->issues()
        ->current();

    expect($collectedIssues->sole()->type)
        ->toBe($issueType);
});

test('a github source collects the pull requests of its organisation with its token', function (): void {
    $source = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, PullRequestsCollector::class)
        ->pullRequests()
        ->current();

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), 'https://api.github.com/search/issues?')
            && str_starts_with($request['q'], sprintf('org:%s ', $organisation))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('a github source reads the files of its organisation with its token', function (string $collectorContract): void {
    $source = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'content' => base64_encode(fake()->text()),
            'encoding' => 'base64',
        ]),
    ]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, $collectorContract)->content(fake()->slug(), fake()->sha1());

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), sprintf('https://api.github.com/repos/%s/', $organisation))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
})->with([
    'composer files' => [ComposerFilesCollector::class],
    'dependabot files' => [DependabotFilesCollector::class],
]);

test('a github source with a setting it needs missing or unset is refused', function (array $sourceConfig): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => $sourceConfig]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, RepositoriesCollector::class);
})->throws(InvalidArgumentException::class)->with([
    'without an organisation' => [
        function (): array {
            return [
                'driver' => 'github',
                'token' => fake()->sha1(),
                'requests_per_minute' => fake()->numberBetween(10, 100),
            ];
        },
    ],
    'with an unset token' => [
        function (): array {
            return [
                'driver' => 'github',
                'organisation' => fake()->slug(),
                'token' => null,
                'requests_per_minute' => fake()->numberBetween(10, 100),
            ];
        },
    ],
    'without requests per minute' => [
        function (): array {
            return [
                'driver' => 'github',
                'organisation' => fake()->slug(),
                'token' => fake()->sha1(),
            ];
        },
    ],
]);

test('a forge source collects the sites of its organisation with its token', function (): void {
    $source = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'server_pattern' => '/.*/',
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, SitesCollector::class)
        ->sites()
        ->current();

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), sprintf('https://forge.laravel.com/api/orgs/%s/sites?', $organisation))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('a forge source collects the domains of a site of its organisation with its token', function (): void {
    $source = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'server_pattern' => '/.*/',
        ],
    ]);
    $site = Site::factory()->create();
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, SiteDomainsCollector::class)->siteDomains($site);

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), sprintf('https://forge.laravel.com/api/orgs/%s/', $organisation))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('a forge source with a setting it needs missing or unset is refused', function (array $sourceConfig): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => $sourceConfig]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, SitesCollector::class);
})->throws(InvalidArgumentException::class)->with([
    'without an organisation' => [
        function (): array {
            return [
                'driver' => 'forge',
                'token' => fake()->sha1(),
                'requests_per_minute' => fake()->numberBetween(10, 100),
                'server_pattern' => '/.*/',
            ];
        },
    ],
    'with an unset token' => [
        function (): array {
            return [
                'driver' => 'forge',
                'organisation' => fake()->slug(),
                'token' => null,
                'requests_per_minute' => fake()->numberBetween(10, 100),
                'server_pattern' => '/.*/',
            ];
        },
    ],
    'without requests per minute' => [
        function (): array {
            return [
                'driver' => 'forge',
                'organisation' => fake()->slug(),
                'token' => fake()->sha1(),
                'server_pattern' => '/.*/',
            ];
        },
    ],
    'without a server pattern' => [
        function (): array {
            return [
                'driver' => 'forge',
                'organisation' => fake()->slug(),
                'token' => fake()->sha1(),
                'requests_per_minute' => fake()->numberBetween(10, 100),
            ];
        },
    ],
]);

test('a compass client source asks a site for its report at its path', function (): void {
    $source = fake()->slug();
    $path = sprintf('/%s', fake()->slug());
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => $path,
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, SiteReportsCollector::class)
        ->siteReport(SiteReport::factory()->create(['path' => null]));

    $http->assertSent(static function (HttpRequest $request) use ($path): bool {
        return str_ends_with($request->url(), $path);
    });
});

test('a compass client source without a path is refused', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
        ],
    ]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->collector($source, SiteReportsCollector::class);
})->throws(InvalidArgumentException::class);
