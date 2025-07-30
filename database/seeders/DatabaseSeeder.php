<?php

namespace Database\Seeders;

use Kdabrow\SeederOnce\SeederOnce;

class DatabaseSeeder extends SeederOnce
{
    public bool $seedOnce = false;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $production = [
            DiscordSettingSeeder::class,
            GameServerSeeder::class,
            MapSeeder::class,
            GameAdminRankSeeder::class,
        ];

        $development = [
            ...$production,
            GameRoundSeeder::class,
            PlayerSeeder::class,
        ];

        $seeders = app()->isProduction() ? $production : $development;
        $this->call($seeders);
    }
}
