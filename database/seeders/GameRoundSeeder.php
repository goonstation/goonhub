<?php

namespace Database\Seeders;

use App\Models\GameRound;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class GameRoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GameRound::insertOrIgnore([
            [
                'id' => 1,
                'server_id' => 'main1',
                'game_type' => 'secret',
                'crashed' => 0,
                'ended_at' => Carbon::now(),
                'created_at' => Carbon::now()->subHour(1),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
