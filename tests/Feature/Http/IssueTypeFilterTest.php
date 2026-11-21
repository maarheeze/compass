<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\IssueTypeFilter;
use Maarheeze\Compass\Models\Issue;

test('issue types filter is filtered on the type or on untyped', function (): void {
    $issueTypesFilter = IssueTypeFilter::issueTypesFilter(
        new FilterOptionCollection(),
        $this->app->make(Translator::class),
    );

    expect($issueTypesFilter->parameterNames)
        ->toBe([IssueTypeFilter::TYPE_PARAMETER, IssueTypeFilter::UNTYPED_PARAMETER]);
});

test('issue types filter holds the issue type filter options', function (): void {
    $issueTypeFilterOptions = new FilterOptionCollection();

    $issueTypesFilter = IssueTypeFilter::issueTypesFilter($issueTypeFilterOptions, $this->app->make(Translator::class));

    expect($issueTypesFilter->options)
        ->toBe($issueTypeFilterOptions);
});

test('issue type route parameters select the type', function (): void {
    $issueType = fake()->randomElement(IssueType::cases());

    $issueTypeRouteParameters = IssueTypeFilter::issueTypeRouteParameters($issueType);

    expect($issueTypeRouteParameters)
        ->toBe([IssueTypeFilter::TYPE_PARAMETER => $issueType->value]);
});

test('untyped route parameters select untyped', function (): void {
    $untypedRouteParameters = IssueTypeFilter::untypedRouteParameters();

    expect($untypedRouteParameters)
        ->toBe([IssueTypeFilter::UNTYPED_PARAMETER => IssueTypeFilter::UNTYPED_VALUE]);
});

test('issues of every type are all the issues', function (): void {
    $issues = Issue::factory()
        ->count(2)
        ->create();
    $issueTypeFilter = IssueTypeFilter::everyType();

    $filteredIssues = $issueTypeFilter->issues(Issue::query())->get();

    expect($filteredIssues->modelKeys())
        ->toEqualCanonicalizing($issues->modelKeys());
});

test('issues of an issue type are the issues of that type', function (): void {
    $bugIssue = Issue::factory()->create(['type' => IssueType::BUG]);
    Issue::factory()->create(['type' => fake()->randomElement([IssueType::FEATURE, IssueType::TASK, null])]);
    $issueTypeFilter = IssueTypeFilter::ofIssueType(IssueType::BUG);

    $filteredIssues = $issueTypeFilter->issues(Issue::query())->get();

    expect($filteredIssues->sole()->is($bugIssue))
        ->toBeTrue();
});

test('issues untyped are the issues without a type', function (): void {
    $untypedIssue = Issue::factory()->create(['type' => null]);
    Issue::factory()->create(['type' => fake()->randomElement(IssueType::cases())]);
    $issueTypeFilter = IssueTypeFilter::untyped();

    $filteredIssues = $issueTypeFilter->issues(Issue::query())->get();

    expect($filteredIssues->sole()->is($untypedIssue))
        ->toBeTrue();
});
