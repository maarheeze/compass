<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubIssuesCollector;

test('issues yields the collected issues of each page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $gitHubIssuesCollector = new GitHubIssuesCollector(new GitHubClient($http, fake()->sha1()), fake()->slug());
    $collectedIssuesPerPage = iterator_to_array($gitHubIssuesCollector->issues());

    expect($collectedIssuesPerPage)
        ->toEqual([new CollectedIssueCollection()]);
});

test('issues throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubIssuesCollector = new GitHubIssuesCollector(new GitHubClient($http, fake()->sha1()), fake()->slug());
    iterator_to_array($gitHubIssuesCollector->issues());
})->throws(CollectingFailed::class);
