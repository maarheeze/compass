<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Http\RunTypeFilter;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunFailure;
use Maarheeze\Compass\Models\RunResult;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\View\FilterOption;

test('listed is the runs newest first', function (): void {
    $earlierRun = Run::factory()->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterRun = Run::factory()->create(['created_at' => fake()->dateTimeBetween('-1 month')]);

    $listedRuns = Run::query()->listed(new ItemsPerPage(fake()->numberBetween(2, 100)));

    expect($listedRuns->first()->id->equals($laterRun->id) && $listedRuns->last()->id->equals($earlierRun->id))
        ->toBeTrue();
});

test('listed loads the failures of the runs', function (): void {
    RunFailure::factory()->create();

    $listedRuns = Run::query()->listed(new ItemsPerPage(fake()->numberBetween(1, 100)));

    expect($listedRuns->sole()->relationLoaded('failures'))
        ->toBeTrue();
});

test('listed loads the results of the runs with their subjects', function (): void {
    RunResult::factory()->create();

    $listedRuns = Run::query()->listed(new ItemsPerPage(fake()->numberBetween(1, 100)));

    $isSubjectLoaded = $listedRuns
        ->sole()
        ->results
        ->sole()
        ->relationLoaded('subject');

    expect($isSubjectLoaded)
        ->toBeTrue();
});

test('listed loads the site of the endpoint a result is of', function (): void {
    RunResult::factory()
        ->for(SiteEndpoint::factory(), 'subject')
        ->create();

    $listedRuns = Run::query()->listed(new ItemsPerPage(fake()->numberBetween(1, 100)));

    $isSiteLoaded = $listedRuns
        ->sole()
        ->results
        ->sole()
        ->subject
        ->siteReport
        ->relationLoaded('site');

    expect($isSiteLoaded)
        ->toBeTrue();
});

test('of collector type is the runs of that collector', function (): void {
    $issuesRun = Run::factory()->create(['collector' => CollectorType::ISSUES]);
    Run::factory()->create(['collector' => CollectorType::PULL_REQUESTS]);

    $issuesRuns = Run::query()
        ->ofCollectorType(CollectorType::ISSUES)
        ->get();

    expect($issuesRuns->sole()->is($issuesRun))
        ->toBeTrue();
});

test('of task type is the runs of that task', function (): void {
    $siteEndpointsRun = Run::factory()->create([
        'source' => null,
        'collector' => null,
        'task' => TaskType::SITE_ENDPOINTS,
    ]);
    Run::factory()->create();

    $siteEndpointsRuns = Run::query()
        ->ofTaskType(TaskType::SITE_ENDPOINTS)
        ->get();

    expect($siteEndpointsRuns->sole()->is($siteEndpointsRun))
        ->toBeTrue();
});

test('run type filter options count the runs of each collector and task in display order', function (): void {
    Run::factory()
        ->count(2)
        ->create(['collector' => CollectorType::SITES]);
    Run::factory()->create([
        'source' => null,
        'collector' => null,
        'task' => TaskType::SITE_ENDPOINTS,
    ]);

    $runTypeFilterOptions = Run::query()->runTypeFilterOptions($this->app->make(Translator::class));

    expect($runTypeFilterOptions->all())
        ->toEqual([
            new FilterOption(
                'Repositories',
                0,
                RunTypeFilter::collectorTypeRouteParameters(CollectorType::REPOSITORIES),
                null,
            ),
            new FilterOption('Issues', 0, RunTypeFilter::collectorTypeRouteParameters(CollectorType::ISSUES), null),
            new FilterOption(
                'Pull requests',
                0,
                RunTypeFilter::collectorTypeRouteParameters(CollectorType::PULL_REQUESTS),
                null,
            ),
            new FilterOption('Sites', 2, RunTypeFilter::collectorTypeRouteParameters(CollectorType::SITES), null),
            new FilterOption(
                'Site reports',
                0,
                RunTypeFilter::collectorTypeRouteParameters(CollectorType::SITE_REPORTS),
                null,
            ),
            new FilterOption('Site endpoints', 1, RunTypeFilter::taskTypeRouteParameters(TaskType::SITE_ENDPOINTS), null),
        ]);
});

test('listed has the given number of runs per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);

    $listedRuns = Run::query()->listed(new ItemsPerPage($itemsPerPage));

    expect($listedRuns->perPage())
        ->toBe($itemsPerPage);
});
