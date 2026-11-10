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

            Route::get('/repositories', [RepositoryController::class, 'index'])->name(RouteName::REPOSITORIES);
            Route::get('/repositories/{repository}', [RepositoryController::class, 'show'])->name(RouteName::REPOSITORY);
            Route::delete('/repositories/{repository}', [RepositoryController::class, 'delete'])->name(RouteName::REPOSITORY_DELETE);
            Route::post('/repositories/{repository}/ignore', [RepositoryController::class, 'ignore'])->name(RouteName::REPOSITORY_IGNORE);
            Route::get('/issues', [IssueController::class, 'index'])->name(RouteName::ISSUES);
            Route::get('/pull-requests', [PullRequestController::class, 'index'])->name(RouteName::PULL_REQUESTS);
            Route::get('/packages/{packageName}', [PackageController::class, 'show'])
                ->where('packageName', '.+')
                ->name(RouteName::PACKAGE);

            Route::prefix('/sites')->group(static function (): void {
                Route::get('/', [SiteController::class, 'index'])->name(RouteName::SITES);
                Route::get('/{site}', [SiteController::class, 'show'])->name(RouteName::SITE);
                Route::delete('/{site}', [SiteController::class, 'delete'])->name(RouteName::SITE_DELETE);
                Route::post('/{site}/ignore', [SiteController::class, 'ignore'])->name(RouteName::SITE_IGNORE);
                Route::get('/{site}/endpoint-checks/{endpointPath}', [SiteEndpointCheckController::class, 'index'])
                    ->name(RouteName::SITE_ENDPOINT_CHECKS);
                Route::delete('/{site}/endpoint-checks/{endpointPath}', [SiteEndpointCheckController::class, 'delete'])
                    ->name(RouteName::SITE_ENDPOINT_CHECKS_DELETE);
                Route::get('/reports/{siteReport}/edit', [SiteReportController::class, 'edit'])
                    ->name(RouteName::SITE_REPORT_EDIT);
                Route::patch('/reports/{siteReport}', [SiteReportController::class, 'update'])
                    ->name(RouteName::SITE_REPORT_UPDATE);
            });

            Route::get('/runs', [RunController::class, 'index'])->name(RouteName::RUNS);
            Route::post('/runs', [RunController::class, 'run'])->name(RouteName::RUNS_RUN);

            Route::get('/settings', [SettingsController::class, 'edit'])->name(RouteName::SETTINGS_EDIT);
            Route::patch('/settings', [SettingsController::class, 'update'])->name(RouteName::SETTINGS_UPDATE);

            Route::post('/logout', [GitHubController::class, 'logout'])->name(RouteName::LOGOUT);
        });
    });
