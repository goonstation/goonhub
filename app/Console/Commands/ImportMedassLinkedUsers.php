<?php

namespace App\Console\Commands;

use App\Libraries\DiscordBot;
use App\Traits\ManagesPlayers;
use App\Traits\ManagesUsers;
use Illuminate\Console\Command;

class ImportMedassLinkedUsers extends Command
{
    use ManagesPlayers, ManagesUsers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:import-medass-linked-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import medass linked users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $response = DiscordBot::export('goonhub/all_links', 'GET');
        $links = collect($response['links']);

        $links = $links->map(function ($link, $ckey) {
            return [
                'ckey' => ckey($ckey),
                'discord_id' => $link['discord_id'],
            ];
        })->filter(function ($link) {
            return $link['discord_id'] !== null;
        })->values();

        $this->info('Found '.count($links).' links to import.');

        $totals = [
            'users' => 0,
            'players' => 0,
            'player_claims' => 0,
            'discord_links' => 0,
            'byond_links' => 0,
        ];

        $report = [
            ['Link Ckey', 'Link Discord', 'User', 'Player', 'Discord Link', 'Byond Link', 'Notes'],
        ];

        foreach ($links as $link) {
            try {
                $result = $this->legacyDiscordLink($link['discord_id'], $link['ckey']);
                $report[] = $result['report'];
                $totals['users'] += $result['totals']['users'];
                $totals['players'] += $result['totals']['players'];
                $totals['player_claims'] += $result['totals']['player_claims'];
                $totals['discord_links'] += $result['totals']['discord_links'];
                $totals['byond_links'] += $result['totals']['byond_links'];
            } catch (\Exception $e) {
                $this->error('Error importing link '.$link['ckey'].': '.$e->getMessage());

                continue;
            }
        }

        $this->table(
            ['Users', 'Players', 'Player claims', 'Discord links', 'Byond links'],
            [$totals]
        );

        $reportFile = fopen(storage_path('app/medass-linked-users-report.csv'), 'w');
        fputcsv($reportFile, $report[0]);
        foreach (array_slice($report, 1) as $row) {
            fputcsv($reportFile, $row);
        }
        fclose($reportFile);

        return Command::SUCCESS;
    }
}
