<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\User\LinkByondController;
use App\Http\Controllers\Web\User\LinkDiscordController;
use Tabuna\Breadcrumbs\Trail;

Route::controller(DashboardController::class)->prefix('/dashboard')->group(function () {
    Route::get('/', 'index')->name('dashboard')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Dashboard', 'web.user.dashboard'));
});

Route::prefix('/user/link')->name('link.')->group(function () {
    Route::controller(LinkByondController::class)->prefix('/byond')->name('byond.')->group(function () {
        Route::get('/redirect', 'redirect')->name('redirect');
        Route::get('/callback', 'callback')->name('callback');
        Route::get('/unlink', 'unlink')->name('unlink');
    });

    Route::controller(LinkDiscordController::class)->prefix('/discord')->name('discord.')->group(function () {
        Route::get('/redirect', 'redirect')->name('redirect');
        Route::get('/callback', 'callback')->name('callback');
        Route::get('/unlink', 'unlink')->name('unlink');
    });
});
