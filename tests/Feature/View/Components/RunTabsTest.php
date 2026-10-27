<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\RunTabs;

test('tabs mark all runs as the only active one on its page', function (): void {
    $request = Request::create(route(RouteName::RUNS));

    $runTabs = $this->app->make(RunTabs::class, ['request' => $request]);

    expect($runTabs->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::RUNS));
});

test('tabs mark the run type as the only active one on its page', function (): void {
    $runType = fake()->randomElement(RunType::cases());
    $request = Request::create(route(RouteName::RUNS_OF_TYPE, $runType));

    $runTabs = $this->app->make(RunTabs::class, ['request' => $request]);

    expect($runTabs->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::RUNS_OF_TYPE, $runType));
});
