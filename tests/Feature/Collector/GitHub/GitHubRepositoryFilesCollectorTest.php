<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubRepositoryFilesCollector;

test('content is the content of the file at its version', function (): void {
    $text = fake()->text();
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => $text,
                    ],
                ],
            ],
        ]),
    ]);

    $gitHubRepositoryFilesCollector = new GitHubRepositoryFilesCollector(
        new GitHubClient($http, fake()->sha1()),
        fake()->slug(),
    );

    expect($gitHubRepositoryFilesCollector->content(fake()->slug(), fake()->sha1()))
        ->toBe($text);
});

test('content throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubRepositoryFilesCollector = new GitHubRepositoryFilesCollector(
        new GitHubClient($http, fake()->sha1()),
        fake()->slug(),
    );
    $gitHubRepositoryFilesCollector->content(fake()->slug(), fake()->sha1());
})->throws(CollectingFailed::class);
