<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\PlayerHos;
use App\Traits\ManagesPlayers;
use App\Traits\ManagesUsers;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Console\Command;

class ImportHos extends Command
{
    use ManagesPlayers, ManagesUsers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:import-hos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import head of security from repo config file';

    public function handle()
    {
        $hos = null;

        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $hos = $conn->repo()->contents()->download(
                config('goonhub.github_organization'),
                'goonstation-secret',
                'config/nt.txt'
            );
        } catch (\Throwable) {
            $this->error('Failed to download hos file');

            return Command::FAILURE;
        }

        if (! $hos) {
            $this->error('Failed to download hos file');

            return Command::FAILURE;
        }

        $hos = collect(explode("\n", $hos))
            ->map(function ($line) {
                return ckey($line);
            })->filter();

        $this->info('Importing '.count($hos).' heads of security');

        $players = Player::whereIn('ckey', $hos)->get();

        $this->info('Found '.count($players).' players matching head of security ckeys');

        $hosInserted = PlayerHos::insertOrIgnore($players->map(function ($player) {
            return [
                'player_id' => $player->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray());

        $this->info('Imported '.$hosInserted.' heads of security ('.count($players) - $hosInserted.' already exist)');

        return Command::SUCCESS;
    }
}
