<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\PlayerMentor;
use App\Traits\ManagesPlayers;
use App\Traits\ManagesUsers;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Console\Command;

class ImportMentors extends Command
{
    use ManagesPlayers, ManagesUsers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:import-mentors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import mentors from repo config file';

    public function handle()
    {
        $mentors = null;

        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $mentors = $conn->repo()->contents()->download(
                config('goonhub.github_organization'),
                'goonstation-secret',
                'config/mentors.txt'
            );
        } catch (\Throwable) {
            $this->error('Failed to download mentors file');

            return Command::FAILURE;
        }

        if (! $mentors) {
            $this->error('Failed to download mentors file');

            return Command::FAILURE;
        }

        $mentors = collect(explode("\n", $mentors))
            ->map(function ($line) {
                return ckey($line);
            })->filter();

        $this->info('Importing '.count($mentors).' mentors');

        $players = Player::whereIn('ckey', $mentors)->get();

        $this->info('Found '.count($players).' players matching mentor ckeys');

        $mentorsInserted = PlayerMentor::insertOrIgnore($players->map(function ($player) {
            return [
                'player_id' => $player->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray());

        $this->info('Imported '.$mentorsInserted.' mentors ('.count($players) - $mentorsInserted.' already exist)');

        return Command::SUCCESS;
    }
}
