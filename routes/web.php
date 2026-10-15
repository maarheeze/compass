<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Maarheeze\Compass\Http\Controllers\GitHubController;
use Maarheeze\Compass\Http\Middleware\Authenticate;
use Maarheeze\Compass\Http\Middleware\RedirectIfAuthenticated;

Route::middleware('web')
    ->prefix(config('compass.path'))
    ->name('compass.')
    ->group(static function (): void {
        Route::middleware(RedirectIfAuthenticated::class)->group(static function (): void {
            Route::view('/login', 'compass::auth.login')->name('login');

            Route::get('/auth/github', [GitHubController::class, 'redirect'])->name('auth.github');
            Route::get('/auth/github/callback', [GitHubController::class, 'callback'])->name('auth.github.callback');
        });

        Route::middleware(Authenticate::class)->group(static function (): void {
            Route::view('/', 'compass::dashboard')->name('dashboard');

            Route::post('/logout', [GitHubController::class, 'logout'])->name('logout');
        });
    });
