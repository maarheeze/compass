<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Maarheeze\Compass\Http\Controllers\GitHubController;
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
            Route::view('/', 'compass::dashboard')->name(RouteName::DASHBOARD);

            Route::post('/logout', [GitHubController::class, 'logout'])->name(RouteName::LOGOUT);
        });
    });
