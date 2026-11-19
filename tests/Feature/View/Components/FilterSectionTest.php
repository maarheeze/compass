<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\View\Components\FilterSection;
use Maarheeze\Compass\View\Filter;
use Maarheeze\Compass\View\FilterOption;

test('option url is the current page with the query parameters of the option', function (): void {
    $filterOption = new FilterOption(fake()->word(), fake()->randomDigitNotZero(), ['server' => 'web-1'], null);
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), ['server'], new FilterOptionCollection([$filterOption])),
        'request' => Request::create('https://compass.test/sites?php=php84'),
    ]);

    $optionUrl = $filterSection->optionUrl($filterOption);

    expect($optionUrl)
        ->toBe('https://compass.test/sites?php=php84&server=web-1');
});

test('option url replaces the current choice of the filter', function (): void {
    $filterOption = new FilterOption(fake()->word(), fake()->randomDigitNotZero(), ['unassigned' => 'true'], null);
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), ['source', 'assignee', 'unassigned'], new FilterOptionCollection([$filterOption])),
        'request' => Request::create('https://compass.test/issues?source=github&assignee=octocat&page=2'),
    ]);

    $optionUrl = $filterSection->optionUrl($filterOption);

    expect($optionUrl)
        ->toBe('https://compass.test/issues?page=2&unassigned=true');
});

test('is selected is true when the page has every query parameter of the option', function (): void {
    $filterOption = new FilterOption(fake()->word(), fake()->randomDigitNotZero(), ['source' => 'github', 'assignee' => 'octocat'], null);
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), ['source', 'assignee'], new FilterOptionCollection([$filterOption])),
        'request' => Request::create('https://compass.test/issues?source=github&assignee=octocat'),
    ]);

    $isSelected = $filterSection->isSelected($filterOption);

    expect($isSelected)
        ->toBeTrue();
});

test('is selected is false when the page lacks a query parameter of the option', function (): void {
    $filterOption = new FilterOption(fake()->word(), fake()->randomDigitNotZero(), ['source' => 'github', 'assignee' => 'octocat'], null);
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), ['source', 'assignee'], new FilterOptionCollection([$filterOption])),
        'request' => Request::create('https://compass.test/issues?source=gitlab&assignee=octocat'),
    ]);

    $isSelected = $filterSection->isSelected($filterOption);

    expect($isSelected)
        ->toBeFalse();
});

test('is filtered is true when the page has a parameter of the filter', function (): void {
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), ['server'], new FilterOptionCollection()),
        'request' => Request::create('https://compass.test/sites?server=web-1'),
    ]);

    expect($filterSection->isFiltered)
        ->toBeTrue();
});

test('is filtered is false when the page has no parameter of the filter', function (): void {
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), ['server'], new FilterOptionCollection()),
        'request' => Request::create('https://compass.test/sites?php=php84'),
    ]);

    expect($filterSection->isFiltered)
        ->toBeFalse();
});

test('unfiltered url is the current page without the parameters of the filter', function (): void {
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), ['source', 'assignee'], new FilterOptionCollection()),
        'request' => Request::create('https://compass.test/issues?source=github&assignee=octocat&page=2'),
    ]);

    expect($filterSection->unfilteredUrl)
        ->toBe('https://compass.test/issues?page=2');
});

test('render renders the filter section view', function (): void {
    $filterSection = $this->app->make(FilterSection::class, [
        'filter' => new Filter(fake()->word(), [fake()->word()], new FilterOptionCollection()),
    ]);

    expect($filterSection->render()->name())
        ->toBe('compass::components.filter-section');
});
