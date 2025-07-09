<?php

namespace App\Traits;

use App\Libraries\DiscordBot;
use App\Models\Player;
use App\Models\PlayerHos;
use App\Models\PlayerMentor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Str;

trait ManagesUsers
{
    use ManagesPlayers;

    private function handleDiscordCallback()
    {
        $user = null;
        $discordUser = Socialite::driver('discord')->user();
        $discordId = $discordUser->getId();
        $discordDetails = DiscordBot::export('goonhub/auth', 'GET', [
            'discord_id' => $discordId,
        ]);
        $discordName = $discordUser->getName();
        $discordEmail = $discordUser->getEmail();

        $user = User::where('discord_id', $discordId)->first();

        if (! $user) {
            // Registering

            if (! $discordEmail || User::where('email', $discordEmail)->exists()) {
                $discordEmail = Str::random(20).'@null.local';
            }

            $user = User::create([
                'name' => $discordName,
                'email' => strtolower($discordEmail),
                'password' => Hash::make(Str::password()),
                'discord_id' => $discordId,
            ]);
        }

        if (! $user->player) {
            // Not associated with a player yet

            $linkedDiscordCkey = isset($discordDetails['ckey']) ? ckey($discordDetails['ckey']) : null;
            $linkedDiscordPlayer = $linkedDiscordCkey ? Player::whereCkey($linkedDiscordCkey)->first() : null;

            $player = null;
            if ($linkedDiscordPlayer && ! $linkedDiscordPlayer->user) {
                // Discord user is linked to a player via Medass
                // And that player is not yet claimed by a user
                $player = $linkedDiscordPlayer;
            } else {
                // Discord user is not linked to a player, create a new one
                // (We have no other way of associating an existing player with a discord user)
                $player = $this->createPlayer($discordName, 'discord');
            }
            $user->player_id = $player->id;
            $user->save();
        }

        // Assign special abilities based on Discord roles
        if (array_key_exists('is_mentor', $discordDetails) && $discordDetails['is_mentor']) {
            PlayerMentor::createOrFirst(['player_id' => $user->player_id]);
        }
        if (array_key_exists('is_hos', $discordDetails) && $discordDetails['is_hos']) {
            PlayerHos::createOrFirst(['player_id' => $user->player_id]);
        }

        return $user;
    }
}
