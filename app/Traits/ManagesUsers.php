<?php

namespace App\Traits;

use App\Jobs\GrantDiscordRole;
use App\Libraries\DiscordBot;
use App\Models\Player;
use App\Models\PlayerHos;
use App\Models\PlayerMentor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

trait ManagesUsers
{
    use ManagesPlayers;

    private function handleDiscordCallback(string $redirectUrl)
    {
        $user = null;
        /** @var \SocialiteProviders\Discord\Provider */
        $driver = Socialite::driver('discord');
        $discordUser = $driver->redirectUrl($redirectUrl)->user();
        $discordId = $discordUser->getId();
        $discordDetails = DiscordBot::export('goonhub/auth', 'GET', [
            'discord_id' => $discordId,
        ]);
        $discordName = $discordUser->getName();
        $discordEmail = $discordUser->getEmail();

        $user = User::whereHas('linkedDiscord', function ($query) use ($discordId) {
            $query->where('discord_id', $discordId);
        })->first();

        if (! $user) {
            // Registering

            $emailLess = false;
            $userEmail = $discordEmail;
            if (! $discordEmail || User::where('email', $discordEmail)->exists()) {
                $userEmail = Str::random(20).'@null.local';
                $emailLess = true;
            }

            $user = User::create([
                'name' => $discordName,
                'email' => strtolower($userEmail),
                'password' => Hash::make(Str::password()),
                'passwordless' => true,
                'emailless' => $emailLess,
            ]);
            $this->linkToDiscord($user, $discordId, $discordName, $discordEmail);
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
                $this->linkToByond($user, $linkedDiscordCkey);
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

    private function linkToByond(User $user, string $ckey)
    {
        try {
            $user->linkedByond()->create([
                'ckey' => $ckey,
            ]);
        } catch (\Throwable $e) {
            //
        }
    }

    private function linkToDiscord(User $user, string $discordId, ?string $discordName = null, ?string $discordEmail = null)
    {
        try {
            $user->linkedDiscord()->create([
                'discord_id' => $discordId,
                'name' => $discordName,
                'email' => $discordEmail,
            ]);
        } catch (\Throwable $e) {
            //
        }

        GrantDiscordRole::dispatch($discordId);
    }
}
