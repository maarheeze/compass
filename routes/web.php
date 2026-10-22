<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Maarheeze\Compass\Http\Controllers\DashboardController;
use Maarheeze\Compass\Http\Controllers\GitHubController;
use Maarheeze\Compass\Http\Controllers\IssueController;
use Maarheeze\Compass\Http\Controllers\PackageController;
use Maarheeze\Compass\Http\Controllers\RepositoryController;
use Maarheeze\Compass\Http\Controllers\SiteController;
use Maarheeze\Compass\Http\Controllers\SyncController;
use Maarheeze\Compass\Http\Middleware\Authenticate;
use Maarheeze\Compass\Http\Middleware\RedirectIfAuthenticated;
use Maarheeze\Compass\Http\RouteName;

Route::middleware('web')
    ->prefix(config('compass.path'))
    ->group(static function (): void {
        Route::middleware(RedirectIfAuthenticated::class)->group(static function (): void {
            Route::view('/login', 'compass::auth.login')->name(RouteName::LOGIN);

            Route::get('/auth/github', [GitHubController::class, 'redirect'])->name(RouteName::GITHUB_REDIRECT);
            Route::get('/auth/github/callback', [GitHubController::class, 'callback'])
                ->name(RouteName::GITHUB_CALLBACK);
        });

        Route::middleware(Authenticate::class)->group(static function (): void {
            Route::get('/', [DashboardController::class, 'show'])->name(RouteName::DASHBOARD);
            Route::get('/github/repositories', [RepositoryController::class, 'index'])->name(RouteName::GITHUB_REPOSITORIES);
            Route::get('/github/repositories/{repository}', [RepositoryController::class, 'show'])
                ->name(RouteName::GITHUB_REPOSITORY);
            Route::get('/github/issues', [IssueController::class, 'index'])->name(RouteName::GITHUB_ISSUES);
            Route::get('/github/issues/assigned', [IssueController::class, 'assigned'])
                ->name(RouteName::GITHUB_ISSUES_ASSIGNED);
            Route::get('/github/packages/{packageName}', [PackageController::class, 'show'])
                ->where('packageName', '.+')
                ->name(RouteName::GITHUB_PACKAGE);
            Route::get('/forge/sites', [SiteController::class, 'index'])->name(RouteName::FORGE_SITES);
            Route::get('/forge/sites/{site}', [SiteController::class, 'show'])->name(RouteName::FORGE_SITE);

            Route::post('/logout', [GitHubController::class, 'logout'])->name(RouteName::LOGOUT);
            Route::post('/sync/github', [SyncController::class, 'github'])->name(RouteName::GITHUB_SYNC);
            Route::post('/sync/forge', [SyncController::class, 'forge'])->name(RouteName::FORGE_SYNC);
        });
    });
