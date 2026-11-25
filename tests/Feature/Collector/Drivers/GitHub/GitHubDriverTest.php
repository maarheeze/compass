<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubDriver;
use Maarheeze\Compass\Collector\Issues\IssueTypeMapping;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\SourceConfig;

test('content is the content of the file at its version', function (): void {
    $text = fake()->text();
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'content' => base64_encode($text),
            'encoding' => 'base64',
        ]),
    ]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );

    expect($gitHubDriver->content(fake()->slug(), fake()->sha1()))
        ->toBe($text);
});

test('content throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );
    $gitHubDriver->content(fake()->slug(), fake()->sha1());
})->throws(CollectingFailed::class);

test('issues yields the collected issues of each page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );
    $collectedIssuesPerPage = iterator_to_array($gitHubDriver->issues());

    expect($collectedIssuesPerPage)
        ->toEqual([new CollectedIssueCollection()]);
});

test('issues throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );
    iterator_to_array($gitHubDriver->issues());
})->throws(CollectingFailed::class);

test('pull requests yields the collected pull requests of each page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );
    $collectedPullRequestsPerPage = iterator_to_array($gitHubDriver->pullRequests());

    expect($collectedPullRequestsPerPage)
        ->toEqual([new CollectedPullRequestCollection()]);
});

test('pull requests throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );
    iterator_to_array($gitHubDriver->pullRequests());
})->throws(CollectingFailed::class);

test('repositories yields the collected repositories of each page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([
        'data' => [
            'organization' => [
                'repositories' => [
                    'pageInfo' => [
                        'hasNextPage' => true,
                        'endCursor' => fake()->sha1(),
                    ],
                    'nodes' => [],
                ],
            ],
        ],
    ]);
    $responseSequence->push([
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
    ]);
    $http->fake(['*' => $responseSequence]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );
    $collectedRepositoriesPerPage = iterator_to_array($gitHubDriver->repositories());

    expect($collectedRepositoriesPerPage)
        ->toEqual([new CollectedRepositoryCollection(), new CollectedRepositoryCollection()]);
});

test('repositories throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubDriver = new GitHubDriver(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
    );
    iterator_to_array($gitHubDriver->repositories());
})->throws(CollectingFailed::class);
