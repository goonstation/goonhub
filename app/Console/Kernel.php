<?php

namespace App\Console;

use App\Jobs\BuildChangelog;
use App\Jobs\ClearOldAudio;
use App\Jobs\ClearOldDectalks;
use App\Jobs\GameBuildOnRepoUpdate;
use App\Jobs\GenerateGlobalPlayerStats;
use App\Jobs\GenerateNumbersStationPass;
use App\Jobs\GetPlayerCounts;
use App\Jobs\UpdateGeoLite;
use App\Jobs\UpdateYoutubeDLP;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        $schedule->job(new ClearOldDectalks)->dailyAt('07:10')->sentryMonitor();
        $schedule->job(new ClearOldAudio)->dailyAt('07:20')->sentryMonitor();

        $schedule->job(new UpdateGeoLite)->weekly()->sentryMonitor();
        $schedule->job(new UpdateYoutubeDLP)->weekly()->sentryMonitor();

        if (App::isProduction()) {
            $schedule->job(new BuildChangelog)->everyFiveMinutes()->sentryMonitor();
            $schedule->job(new GetPlayerCounts)->everyFiveMinutes()->sentryMonitor();
            $schedule->job(new GameBuildOnRepoUpdate)->everyFiveMinutes()->sentryMonitor();

            $schedule->job(new GenerateNumbersStationPass)->hourly()->sentryMonitor();

            $schedule->job(new GenerateGlobalPlayerStats)->daily()->sentryMonitor();
        }

        if (App::environment(['production', 'staging'])) {
            $schedule->command(DispatchQueueCheckJobsCommand::class)->everyMinute();
            $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
        }

        if (App::isLocal()) {
            $schedule->command('telescope:prune')->daily();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
