<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\CollectorRegistry;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Uuid\Uuid;

test('the job of each collector type is registered', function (
    CollectorType $collectorType,
    string $jobClass,
): void {
    $collectorRegistry = $this->app->make(CollectorRegistry::class);

    expect($collectorRegistry->job($collectorType, Uuid::generate()))
        ->toBeInstanceOf($jobClass);
})->with([
    'issues' => [CollectorType::ISSUES, CollectIssues::class],
    'pull requests' => [CollectorType::PULL_REQUESTS, CollectPullRequests::class],
    'repositories' => [CollectorType::REPOSITORIES, CollectRepositories::class],
    'sites' => [CollectorType::SITES, CollectSites::class],
]);
