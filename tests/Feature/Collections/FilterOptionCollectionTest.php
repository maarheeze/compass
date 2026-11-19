<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\View\FilterOption;

test('count option adds an option not counted before with a count of one', function (): void {
    $label = fake()->word();
    $queryParameters = [fake()->word() => fake()->slug()];
    $avatarUrl = fake()->imageUrl();
    $filterOptions = new FilterOptionCollection();

    $filterOptions->countOption($label, $queryParameters, $avatarUrl);

    expect($filterOptions->sole())
        ->toEqual(new FilterOption($label, 1, $queryParameters, $avatarUrl));
});

test('count option raises the count of an option with the same query parameters', function (): void {
    $queryParameters = [fake()->word() => fake()->slug()];
    $filterOptions = new FilterOptionCollection();
    $filterOptions->countOption(fake()->word(), $queryParameters, null);

    $filterOptions->countOption(fake()->word(), $queryParameters, null);

    expect($filterOptions->sole()->count)
        ->toBe(2);
});

test('count option counts an option with other query parameters apart', function (): void {
    $parameterName = fake()->word();
    $filterOptions = new FilterOptionCollection();
    $filterOptions->countOption(fake()->word(), [$parameterName => fake()->unique()->slug()], null);

    $filterOptions->countOption(fake()->word(), [$parameterName => fake()->unique()->slug()], null);

    expect($filterOptions)
        ->toHaveCount(2);
});

test('sorted by label is the options in the natural order of their label', function (): void {
    $filterOptions = new FilterOptionCollection();
    $filterOptions->countOption('web-10', ['server' => 'web-10'], null);
    $filterOptions->countOption('Web-9', ['server' => 'Web-9'], null);
    $filterOptions->countOption('web-2', ['server' => 'web-2'], null);

    $filterOptionsSortedByLabel = $filterOptions->sortedByLabel();

    expect($filterOptionsSortedByLabel->pluck('label')->all())
        ->toBe(['web-2', 'Web-9', 'web-10']);
});

test('sorted by label descending is the options in the reversed natural order of their label', function (): void {
    $filterOptions = new FilterOptionCollection();
    $filterOptions->countOption('php83', ['php' => 'php83'], null);
    $filterOptions->countOption('php84', ['php' => 'php84'], null);
    $filterOptions->countOption('php74', ['php' => 'php74'], null);

    $filterOptionsSortedByLabelDescending = $filterOptions->sortedByLabelDescending();

    expect($filterOptionsSortedByLabelDescending->pluck('label')->all())
        ->toBe(['php84', 'php83', 'php74']);
});
