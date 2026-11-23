<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Http\RunTypeFilter;
use Maarheeze\Compass\Models\Run;

test('run types filter is filtered on the collector or on the task', function (): void {
    $runTypesFilter = RunTypeFilter::runTypesFilter(new FilterOptionCollection(), $this->app->make(Translator::class));

    expect($runTypesFilter->parameterNames)
        ->toBe([RunTypeFilter::COLLECTOR_PARAMETER, RunTypeFilter::TASK_PARAMETER]);
});

test('run types filter holds the run type filter options', function (): void {
    $runTypeFilterOptions = new FilterOptionCollection();

    $runTypesFilter = RunTypeFilter::runTypesFilter($runTypeFilterOptions, $this->app->make(Translator::class));

    expect($runTypesFilter->options)
        ->toBe($runTypeFilterOptions);
});

test('collector type route parameters select the collector', function (): void {
    $collectorType = fake()->randomElement(CollectorType::cases());

    $collectorTypeRouteParameters = RunTypeFilter::collectorTypeRouteParameters($collectorType);

    expect($collectorTypeRouteParameters)
        ->toBe([RunTypeFilter::COLLECTOR_PARAMETER => $collectorType->value]);
});

test('task type route parameters select the task', function (): void {
    $taskType = fake()->randomElement(TaskType::cases());

    $taskTypeRouteParameters = RunTypeFilter::taskTypeRouteParameters($taskType);

    expect($taskTypeRouteParameters)
        ->toBe([RunTypeFilter::TASK_PARAMETER => $taskType->value]);
});

test('runs of every type are all the runs', function (): void {
    $runs = Run::factory()
        ->count(2)
        ->create();
    $runTypeFilter = RunTypeFilter::everyType();

    $filteredRuns = $runTypeFilter->runs(Run::query())->get();

    expect($filteredRuns->modelKeys())
        ->toEqualCanonicalizing($runs->modelKeys());
});

test('runs of a collector type are the runs of that collector', function (): void {
    $sitesRun = Run::factory()->create(['collector' => CollectorType::SITES]);
    Run::factory()->create(['collector' => CollectorType::REPOSITORIES]);
    $runTypeFilter = RunTypeFilter::ofCollectorType(CollectorType::SITES);

    $filteredRuns = $runTypeFilter->runs(Run::query())->get();

    expect($filteredRuns->sole()->is($sitesRun))
        ->toBeTrue();
});

test('runs of a task type are the runs of that task', function (): void {
    $siteEndpointsRun = Run::factory()->create([
        'source' => null,
        'collector' => null,
        'task' => TaskType::SITE_ENDPOINTS,
    ]);
    Run::factory()->create();
    $runTypeFilter = RunTypeFilter::ofTaskType(TaskType::SITE_ENDPOINTS);

    $filteredRuns = $runTypeFilter->runs(Run::query())->get();

    expect($filteredRuns->sole()->is($siteEndpointsRun))
        ->toBeTrue();
});
