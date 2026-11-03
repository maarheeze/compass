<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubPullRequestsCollector;

test('pull requests yields the collected pull requests of each page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([], 200, [
        'Link' => sprintf('<%s>; rel="next"', fake()->url()),
    ]);
    $responseSequence->push([]);
    $http->fake(['*' => $responseSequence]);

    $gitHubPullRequestsCollector = new GitHubPullRequestsCollector(
        new GitHubClient($http, fake()->sha1()),
        fake()->slug(),
    );
    $collectedPullRequestsPerPage = iterator_to_array($gitHubPullRequestsCollector->pullRequests());

    expect($collectedPullRequestsPerPage)
        ->toEqual([new CollectedPullRequestCollection(), new CollectedPullRequestCollection()]);
});

test('pull requests throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubPullRequestsCollector = new GitHubPullRequestsCollector(
        new GitHubClient($http, fake()->sha1()),
        fake()->slug(),
    );
    iterator_to_array($gitHubPullRequestsCollector->pullRequests());
})->throws(CollectingFailed::class);
