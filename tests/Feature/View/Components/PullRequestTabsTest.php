<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\PullRequestTabs;

test('tabs mark all open as the only active one on its page', function (): void {
    $request = Request::create(route(RouteName::GITHUB_PULL_REQUESTS));
    $request->setRouteResolver(function () use ($request): Route {
        return $this->app
            ->make(Router::class)
            ->getRoutes()
            ->match($request);
    });

    $pullRequestTabs = $this->app->make(PullRequestTabs::class, ['request' => $request]);

    expect($pullRequestTabs->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::GITHUB_PULL_REQUESTS));
});

test('tabs mark assigned to me as the only active one on its page', function (): void {
    $request = Request::create(route(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED));
    $request->setRouteResolver(function () use ($request): Route {
        return $this->app
            ->make(Router::class)
            ->getRoutes()
            ->match($request);
    });

    $pullRequestTabs = $this->app->make(PullRequestTabs::class, ['request' => $request]);

    expect($pullRequestTabs->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED));
});
