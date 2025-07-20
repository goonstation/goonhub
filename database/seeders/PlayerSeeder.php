<?php

namespace Database\Seeders;

use App\Models\GameRound;
use App\Models\Player;
use App\Models\PlayerConnection;
use App\Models\PlayerParticipation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameRound>
     */
    private Collection $gameRounds;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->gameRounds = GameRound::all();

        Player::factory(100)
            ->create()
            ->each(function (Player $player) {
                $availableRounds = $this->gameRounds->where('created_at', '>=', $player->created_at);
                $amount = min(10, $availableRounds->count());

                $connectionFactory = PlayerConnection::factory();
                $connectionDefinition = collect($connectionFactory->definition());

                for ($i = 0; $i < $amount; $i++) {
                    $round = $availableRounds->values()->get($i);
                    $createdAt = fake()->dateTimeBetween($round->created_at, $round->ended_at);

                    PlayerConnection::factory(1)
                        ->create([
                            ...$connectionDefinition->only(['ip', 'comp_id', 'country', 'country_iso']),
                            'player_id' => $player->id,
                            'round_id' => $round->id,
                            'created_at' => $createdAt,
                        ]);

                    PlayerParticipation::factory(1)
                        ->create([
                            'player_id' => $player->id,
                            'round_id' => $round->id,
                            'created_at' => $createdAt,
                        ]);
                }
            });
    }
}
