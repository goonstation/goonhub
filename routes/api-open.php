<?php

use App\Http\Controllers\Api\GameServersController;
use Illuminate\Support\Facades\Route;

Route::get('servers', [GameServersController::class, 'index'])->name('servers.index')->withoutMiddleware('throttle:api');
