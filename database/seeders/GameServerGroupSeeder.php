<?php

namespace Database\Seeders;

use App\Models\GameServerGroup;
use Kdabrow\SeederOnce\SeederOnce;

class GameServerGroupSeeder extends SeederOnce
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GameServerGroup::insertOrIgnore([
            ['name' => 'Default'],
            ['name' => 'Streamer'],
        ]);
    }
}
