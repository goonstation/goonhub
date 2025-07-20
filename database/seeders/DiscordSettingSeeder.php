<?php

namespace Database\Seeders;

use App\Models\DiscordSetting;
use Kdabrow\SeederOnce\SeederOnce;

class DiscordSettingSeeder extends SeederOnce
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiscordSetting::insertOrIgnore([
            [
                'key' => DiscordSetting::GRANT_ROLE_WHEN_LINKED,
                'name' => 'Grant Role When Linked',
                'description' => 'Grant a role to a user when they link their Discord account.',
            ],
        ]);
    }
}
