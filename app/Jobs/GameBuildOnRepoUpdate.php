<?php

namespace App\Jobs;

use App\Jobs\GameBuild as GameBuildJob;
use App\Models\GameBuild;
use App\Models\GameBuildSetting;
use App\Models\PlayerAdmin;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GameBuildOnRepoUpdate implements ShouldQueue
{
    use Queueable;

    protected $timeout = 30;

    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $settings = GameBuildSetting::with(['gameServer'])->whereRelation('gameServer', 'active', true)->get();
        $branches = $settings->unique('branch')->pluck('branch');

        $latestRemoteCommits = [];
        foreach ($branches as $branch) {
            try {
                /** @var \Github\Client */
                $conn = GitHub::connection();
                $latestCommit = $conn->repo()->commits()->show(
                    config('goonhub.github_organization'),
                    config('goonhub.github_repo'),
                    $branch
                );
                $latestRemoteCommits[$branch] = $latestCommit['sha'];
            } catch (\Throwable) {
                continue;
            }
        }

        $botAdmin = PlayerAdmin::whereRelation('rank', 'rank', 'Bot')->first();
        foreach ($settings as $setting) {
            if (! array_key_exists($setting->branch, $latestRemoteCommits)) {
                continue;
            }

            $latestBuild = GameBuild::where('server_id', $setting->gameServer->server_id)->latest()->first();

            if (! $latestBuild || $latestBuild->commit !== $latestRemoteCommits[$setting->branch]) {
                GameBuildJob::dispatch($botAdmin, $setting->gameServer);
            }
        }
    }
}
