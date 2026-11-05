<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Maarheeze\Compass\Http\Controllers\AlertController;
use Maarheeze\Compass\Http\Controllers\DashboardController;
use Maarheeze\Compass\Http\Controllers\GitHubController;
use Maarheeze\Compass\Http\Controllers\IssueController;
use Maarheeze\Compass\Http\Controllers\PackageController;
use Maarheeze\Compass\Http\Controllers\PullRequestController;
use Maarheeze\Compass\Http\Controllers\RepositoryController;
use Maarheeze\Compass\Http\Controllers\RunController;
use Maarheeze\Compass\Http\Controllers\SettingsController;
use Maarheeze\Compass\Http\Controllers\SiteController;
use Maarheeze\Compass\Http\Controllers\SiteEndpointCheckController;
use Maarheeze\Compass\Http\Controllers\SiteReportController;
use Maarheeze\Compass\Http\Middleware\Authenticate;
use Maarheeze\Compass\Http\Middleware\RedirectIfAuthenticated;
use Maarheeze\Compass\Http\RouteName;

Route::middleware('web')
    ->prefix(config('compass.path'))
    ->group(static function (): void {
        Route::middleware(RedirectIfAuthenticated::class)->group(static function (): void {
            Route::view('/login', 'compass::auth.login')->name(RouteName::LOGIN);

            Route::get('/auth/github', [GitHubController::class, 'redirect'])->name(RouteName::GITHUB_REDIRECT);
            Route::get('/auth/github/callback', [GitHubController::class, 'callback'])->name(RouteName::GITHUB_CALLBACK);
        });

        Route::middleware(Authenticate::class)->group(static function (): void {
            Route::get('/', [DashboardController::class, 'show'])->name(RouteName::DASHBOARD);

            Route::prefix('/alerts/{alert}')->group(static function (): void {
                Route::get('/', [AlertController::class, 'show'])->name(RouteName::ALERT);
                Route::post('/assign', [AlertController::class, 'assign'])->name(RouteName::ALERT_ASSIGN);
                Route::post('/mark-done', [AlertController::class, 'markDone'])->name(RouteName::ALERT_MARK_DONE);
                Route::post('/mute', [AlertController::class, 'mute'])->name(RouteName::ALERT_MUTE);
                Route::post('/reopen', [AlertController::class, 'reopen'])->name(RouteName::ALERT_REOPEN);
                Route::post('/unassign', [AlertController::class, 'unassign'])->name(RouteName::ALERT_UNASSIGN);
                Route::post('/unmute', [AlertController::class, 'unmute'])->name(RouteName::ALERT_UNMUTE);
            });

            Route::prefix('/github')->group(static function (): void {
                Route::get('/repositories', [RepositoryController::class, 'index'])->name(RouteName::GITHUB_REPOSITORIES);
                Route::get('/repositories/{repository}', [RepositoryController::class, 'show'])->name(RouteName::GITHUB_REPOSITORY);
                Route::delete('/repositories/{repository}', [RepositoryController::class, 'delete'])->name(RouteName::GITHUB_REPOSITORY_DELETE);
                Route::post('/repositories/{repository}/ignore', [RepositoryController::class, 'ignore'])->name(RouteName::GITHUB_REPOSITORY_IGNORE);
                Route::get('/issues', [IssueController::class, 'index'])->name(RouteName::GITHUB_ISSUES);
                Route::get('/issues/assigned', [IssueController::class, 'assigned'])->name(RouteName::GITHUB_ISSUES_ASSIGNED);
                Route::get('/pull-requests', [PullRequestController::class, 'index'])->name(RouteName::GITHUB_PULL_REQUESTS);
                Route::get('/pull-requests/assigned', [PullRequestController::class, 'assigned'])->name(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED);
                Route::get('/packages/{packageName}', [PackageController::class, 'show'])
                    ->where('packageName', '.+')
                    ->name(RouteName::GITHUB_PACKAGE);
            });

            Route::prefix('/forge/sites')->group(static function (): void {
                Route::get('/', [SiteController::class, 'index'])->name(RouteName::FORGE_SITES);
                Route::get('/{site}', [SiteController::class, 'show'])->name(RouteName::FORGE_SITE);
                Route::delete('/{site}', [SiteController::class, 'delete'])->name(RouteName::FORGE_SITE_DELETE);
                Route::post('/{site}/ignore', [SiteController::class, 'ignore'])->name(RouteName::FORGE_SITE_IGNORE);
                Route::get('/{site}/endpoint-checks/{endpointPathRouteKey}', [SiteEndpointCheckController::class, 'index'])
                    ->name(RouteName::FORGE_SITE_ENDPOINT_CHECKS);
                Route::delete('/{site}/endpoint-checks/{endpointPathRouteKey}', [SiteEndpointCheckController::class, 'delete'])
                    ->name(RouteName::FORGE_SITE_ENDPOINT_CHECKS_DELETE);
                Route::get('/reports/{siteReport}/edit', [SiteReportController::class, 'edit'])
                    ->name(RouteName::FORGE_SITE_REPORT_EDIT);
                Route::patch('/reports/{siteReport}', [SiteReportController::class, 'update'])
                    ->name(RouteName::FORGE_SITE_REPORT_UPDATE);
            });

            Route::get('/runs', [RunController::class, 'index'])->name(RouteName::RUNS);
            Route::get('/runs/{runType}', [RunController::class, 'type'])->name(RouteName::RUNS_OF_TYPE);

            Route::get('/settings', [SettingsController::class, 'edit'])->name(RouteName::SETTINGS_EDIT);
            Route::patch('/settings', [SettingsController::class, 'update'])->name(RouteName::SETTINGS_UPDATE);

            Route::post('/logout', [GitHubController::class, 'logout'])->name(RouteName::LOGOUT);
        });
    });
