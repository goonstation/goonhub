<?php

use App\Jobs\GetAllGameStatus;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\RunHealthChecksCommand;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Schedule::command(RunHealthChecksCommand::class)->everyMinute();

if (App::isProduction()) {
    Schedule::job(GetAllGameStatus::class)->everyThirtySeconds();
}
