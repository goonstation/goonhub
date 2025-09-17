<?php

namespace App\Jobs;

use App\Enums\DiscordSettings;
use App\Facades\DiscordApi;
use App\Models\DiscordSetting;
use App\Models\GameServerGroup;
use App\Models\Player;
use App\Traits\ManagesWhitelist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddTomatoSubscriberToWhitelist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ManagesWhitelist;

    public $tries = 1;

    public function __construct(
        public string $userDiscordId,
        public Player $player,
    ) {}

    public function handle()
    {
        $serverGroup = GameServerGroup::where('name', 'Streamer')->first();

        $guildId = DiscordSetting::where('key', DiscordSettings::TOMATO_GUILD_ID->value)->first()?->value;
        $subscriberRoleId = DiscordSetting::where('key', DiscordSettings::TOMATO_SUBSCRIBER_ROLE_ID->value)->first()?->value;

        $guild = DiscordApi::guild($guildId);
        $member = $guild->member($this->userDiscordId);
        $roles = $member->json('roles', []);

        if (in_array($subscriberRoleId, $roles)) {
            $whitelist = $this->addPlayerToWhitelist($this->player);
            $whitelist->addServerGroup($serverGroup);
        }
    }
}
