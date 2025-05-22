<?php

use App\Http\Controllers\Api\HealthcheckController;
use Illuminate\Support\Facades\Route;

Route::get('healthcheck', [HealthcheckController::class, 'index']);
