<?php

namespace Database\Seeders;

use App\Enums\DiscordSettings;
use App\Models\DiscordSetting;
use Kdabrow\SeederOnce\SeederOnce;

class TomatoSeeder extends SeederOnce
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiscordSetting::insertOrIgnore([
            [
                'key' => DiscordSettings::TOMATO_GUILD_ID->value,
                'name' => 'Tomato Guild ID',
                'description' => 'The ID of the Tomato Guild.',
            ],
            [
                'key' => DiscordSettings::TOMATO_SUBSCRIBER_ROLE_ID->value,
                'name' => 'Tomato Subscriber Role ID',
                'description' => 'The ID of the Tomato Subscriber Role.',
            ],
        ]);
    }
}
