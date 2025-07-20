<?php

namespace Database\Seeders;

use App\Models\GameRound;
use App\Models\GameServer;
use App\Models\Map;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class GameRoundSeeder extends Seeder
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer>
     */
    private Collection $gameServers;

    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Map>
     */
    private Collection $maps;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->gameServers = GameServer::where('active', true)->where('invisible', false)->get();
        $this->maps = Map::where('active', true)->where('is_layer', false)->get();

        $amount = 100;
        $startDate = now()->subYear();
        $interval = $startDate->diffInDays(now()) / $amount;
        $variance = $interval / 2;

        GameRound::factory($amount)
            ->sequence(function (Sequence $sequence) use ($startDate, $interval, $variance) {
                $createdAt = $startDate->clone();
                $startDate->addDays($interval);
                if ($sequence->index > 0) {
                    $createdAt = Carbon::make(fake()->dateTimeBetween(
                        $startDate->clone()->subDays($variance),
                        $startDate->clone()->addDays($variance)
                    ));
                }

                $endedAt = fake()->dateTimeBetween(
                    $createdAt->clone()->addMinutes(30),
                    $createdAt->clone()->addMinutes(90)
                );

                return [
                    'server_id' => $this->gameServers->random()->server_id,
                    'map' => $this->maps->random()->map_id,
                    'created_at' => $createdAt,
                    'updated_at' => $endedAt,
                    'ended_at' => $endedAt,
                ];
            })
            ->create();
    }
}
