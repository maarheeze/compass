<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Uuid\Uuid;

test('job is the job that collects the collector type', function (
    CollectorType $collectorType,
    string $jobClass,
): void {
    $job = $collectorType->job(Uuid::generate(), fake()->slug());

    expect($job)
        ->toBeInstanceOf($jobClass);
})->with([
    'issues' => [CollectorType::ISSUES, CollectIssues::class],
    'pull requests' => [CollectorType::PULL_REQUESTS, CollectPullRequests::class],
    'repositories' => [CollectorType::REPOSITORIES, CollectRepositories::class],
    'site reports' => [CollectorType::SITE_REPORTS, InspectSites::class],
    'sites' => [CollectorType::SITES, CollectSites::class],
]);
