<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Maarheeze\Compass\Http\Controllers\DashboardController;
use Maarheeze\Compass\Http\Controllers\GitHubController;
use Maarheeze\Compass\Http\Controllers\IssueController;
use Maarheeze\Compass\Http\Controllers\PackageController;
use Maarheeze\Compass\Http\Controllers\PullRequestController;
use Maarheeze\Compass\Http\Controllers\RepositoryController;
use Maarheeze\Compass\Http\Controllers\SiteClientController;
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
            Route::delete('/github/repositories/{repository}', [RepositoryController::class, 'delete'])
                ->name(RouteName::GITHUB_REPOSITORY_DELETE);
            Route::post('/github/repositories/{repository}/ignore', [RepositoryController::class, 'ignore'])
                ->name(RouteName::GITHUB_REPOSITORY_IGNORE);
            Route::get('/github/issues', [IssueController::class, 'index'])->name(RouteName::GITHUB_ISSUES);
            Route::get('/github/issues/assigned', [IssueController::class, 'assigned'])
                ->name(RouteName::GITHUB_ISSUES_ASSIGNED);
            Route::get('/github/pull-requests', [PullRequestController::class, 'index'])
                ->name(RouteName::GITHUB_PULL_REQUESTS);
            Route::get('/github/pull-requests/assigned', [PullRequestController::class, 'assigned'])
                ->name(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED);
            Route::get('/github/packages/{packageName}', [PackageController::class, 'show'])
                ->where('packageName', '.+')
                ->name(RouteName::GITHUB_PACKAGE);

            Route::get('/forge/sites', [SiteController::class, 'index'])->name(RouteName::FORGE_SITES);
            Route::get('/forge/sites/{site}', [SiteController::class, 'show'])->name(RouteName::FORGE_SITE);
            Route::delete('/forge/sites/{site}', [SiteController::class, 'delete'])->name(RouteName::FORGE_SITE_DELETE);
            Route::post('/forge/sites/{site}/ignore', [SiteController::class, 'ignore'])->name(RouteName::FORGE_SITE_IGNORE);
            Route::get('/forge/sites/{site}/client/edit', [SiteClientController::class, 'edit'])
                ->name(RouteName::FORGE_SITE_CLIENT_EDIT);
            Route::patch('/forge/sites/{site}/client', [SiteClientController::class, 'update'])
                ->name(RouteName::FORGE_SITE_CLIENT_UPDATE);

            Route::post('/logout', [GitHubController::class, 'logout'])->name(RouteName::LOGOUT);

            Route::post('/sync', SyncController::class)->name(RouteName::SYNC);
        });
    });
