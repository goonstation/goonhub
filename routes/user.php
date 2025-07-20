<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\User\LinkByondController;
use App\Http\Controllers\Web\User\LinkDiscordController;
use Tabuna\Breadcrumbs\Trail;

Route::controller(DashboardController::class)->prefix('/dashboard')->group(function () {
    Route::get('/', 'index')->name('dashboard')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Dashboard', route('dashboard')));
});

Route::prefix('/user/link')->group(function () {
    Route::controller(LinkByondController::class)->prefix('/byond')->group(function () {
        Route::get('/redirect', 'redirect')->name('link-byond.redirect');
        Route::get('/callback', 'callback')->name('link-byond.callback');
        Route::get('/unlink', 'unlink')->name('link-byond.unlink');
    });

    Route::controller(LinkDiscordController::class)->prefix('/discord')->group(function () {
        Route::get('/redirect', 'redirect')->name('link-discord.redirect');
        Route::get('/callback', 'callback')->name('link-discord.callback');
        Route::get('/unlink', 'unlink')->name('link-discord.unlink');
    });
});
