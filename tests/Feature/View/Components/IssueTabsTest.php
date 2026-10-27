<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\IssueTabs;

test('tabs mark all open as the only active one on its page', function (): void {
    $request = Request::create(route(RouteName::GITHUB_ISSUES));
    $request->setRouteResolver(function () use ($request): Route {
        return $this->app
            ->make(Router::class)
            ->getRoutes()
            ->match($request);
    });

    $issueTabs = $this->app->make(IssueTabs::class, ['request' => $request]);

    expect($issueTabs->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::GITHUB_ISSUES));
});

test('tabs mark assigned to me as the only active one on its page', function (): void {
    $request = Request::create(route(RouteName::GITHUB_ISSUES_ASSIGNED));
    $request->setRouteResolver(function () use ($request): Route {
        return $this->app
            ->make(Router::class)
            ->getRoutes()
            ->match($request);
    });

    $issueTabs = $this->app->make(IssueTabs::class, ['request' => $request]);

    expect($issueTabs->tabs->where('isActive', true)->sole()->url)
        ->toBe(route(RouteName::GITHUB_ISSUES_ASSIGNED));
});
