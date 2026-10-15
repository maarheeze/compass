<?php

use Illuminate\Support\Facades\Route;
use Maarheeze\Compass\Http\Controllers\GitHubController;
use Maarheeze\Compass\Http\Middleware\Authenticate;
use Maarheeze\Compass\Http\Middleware\RedirectIfAuthenticated;

Route::middleware('web')
    ->prefix(config('compass.path'))
    ->name('compass.')
    ->group(function () {
        Route::middleware(RedirectIfAuthenticated::class)->group(function () {
            Route::view('/login', 'compass::auth.login')->name('login');

            Route::get('/auth/github', [GitHubController::class, 'redirect'])->name('auth.github');
            Route::get('/auth/github/callback', [GitHubController::class, 'callback'])->name('auth.github.callback');
        });

        Route::middleware(Authenticate::class)->group(function () {
            Route::view('/', 'compass::dashboard')->name('dashboard');

            Route::post('/logout', [GitHubController::class, 'logout'])->name('logout');
        });
    });
