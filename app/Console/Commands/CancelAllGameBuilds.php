<?php

namespace App\Console\Commands;

use App\Jobs\GameBuild as GameBuildJob;
use App\Libraries\GameBuilder\Build as GameBuildBuild;
use App\Models\GameAdmin;
use App\Traits\ManagesGameBuilds;
use Illuminate\Console\Command;

class CancelAllGameBuilds extends Command
{
    use ManagesGameBuilds;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:cancel-builds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel all running game builds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $buildStatus = $this->getStatus();
        $botAdmin = GameAdmin::whereRelation('rank', 'rank', 'Bot')->first();

        foreach ($buildStatus['current'] as $build) {
            GameBuildBuild::cancel($build->offsetGet('server')->server_id, $botAdmin->id, 'Server shutting down');
        }

        foreach ($buildStatus['queued'] as $build) {
            GameBuildJob::cancelQueuedBuild($build->offsetGet('server')->server_id, $botAdmin->id);
        }
    }
}
