<?php

namespace Database\Seeders;

use App\Models\GameServer;
use Kdabrow\SeederOnce\SeederOnce;

class GameServerSeeder extends SeederOnce
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GameServer::insertOrIgnore([
            [
                'server_id' => 'main1',
                'name' => 'Goonstation 1 Classic: Heisenbee',
                'short_name' => 'Goon 1',
                'address' => 'goon1.goonhub.com',
                'port' => 26100,
                'active' => true,
                'invisible' => false,
                'group_id' => 1,
            ],
            [
                'server_id' => 'main2',
                'name' => 'Goonstation 2 Classic: Bombini',
                'short_name' => 'Goon 2',
                'address' => 'goon2.goonhub.com',
                'port' => 26200,
                'active' => false,
                'invisible' => false,
                'group_id' => 1,
            ],
            [
                'server_id' => 'main3',
                'name' => 'Goonstation 3 Roleplay: Morty',
                'short_name' => 'Goon 3 RP',
                'address' => 'goon3.goonhub.com',
                'port' => 26300,
                'active' => true,
                'invisible' => false,
                'group_id' => 1,
            ],
            [
                'server_id' => 'main4',
                'name' => 'Goonstation 4 Roleplay: Sylvester',
                'short_name' => 'Goon 4 RP',
                'address' => 'goon4.goonhub.com',
                'port' => 26400,
                'active' => true,
                'invisible' => false,
                'group_id' => 1,
            ],
            [
                'server_id' => 'main5',
                'name' => 'Goonstation 5 Event: Rocko',
                'short_name' => 'Goon 5 Event',
                'address' => 'goon5.goonhub.com',
                'port' => 26500,
                'active' => true,
                'invisible' => false,
                'group_id' => 1,
            ],
            [
                'server_id' => 'dev',
                'name' => 'Goonstation Development',
                'short_name' => 'Goon Dev',
                'address' => 'goondev.goonhub.com',
                'port' => 26900,
                'active' => true,
                'invisible' => true,
                'group_id' => 1,
            ],
            [
                'server_id' => 'local',
                'name' => 'Goonstation Local',
                'short_name' => 'Goon Local',
                'address' => '127.0.0.1',
                'port' => 4999,
                'active' => false,
                'invisible' => true,
                'group_id' => 1,
            ],
        ]);
    }
}
