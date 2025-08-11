<?php

namespace App\Traits;

use App\Jobs\GrantDiscordRole;
use App\Models\LinkedByondUser;
use App\Models\LinkedDiscordUser;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            // Discord user is not linked to a player, create a new one
            // (We have no other way of associating an existing player with a discord user)
            $player = $this->createPlayer($discordName, 'discord');
            $user->player_id = $player->id;
            $user->save();
        }

        return $user;
    }

    private function linkToByond(User $user, string $ckey)
    {
        try {
            return $user->linkedByond()->create([
                'ckey' => $ckey,
            ]);
        } catch (\Throwable $e) {
            //
        }

        return null;
    }

    private function linkToDiscord(User $user, string $discordId, ?string $discordName = null, ?string $discordEmail = null)
    {
        $linkedDiscord = null;

        try {
            $linkedDiscord = $user->linkedDiscord()->create([
                'discord_id' => $discordId,
                'name' => $discordName,
                'email' => $discordEmail,
            ]);
        } catch (\Throwable $e) {
            //
        }

        GrantDiscordRole::dispatch($discordId);

        return $linkedDiscord;
    }

    private function legacyDiscordLink(string $discordId, string $ckey)
    {
        $notes = [];

        $linkedDiscord = LinkedDiscordUser::where('discord_id', $discordId)->first();
        $linkedByond = LinkedByondUser::where('ckey', $ckey)->first();

        $player = Player::where('ckey', $ckey)->first();
        $user = $linkedDiscord ? $linkedDiscord->user : ($linkedByond ? $linkedByond->user : null);

        if ($user && $user->linkedDiscord && $user->linkedDiscord->discord_id !== $discordId) {
            $notes[] = 'User '.$user->name.' is already linked to discord '.$user->linkedDiscord->discord_id.' but trying to link to '.$discordId;
        }

        if ($user && $user->linkedByond && $user->linkedByond->ckey !== $ckey) {
            $notes[] = 'User '.$user->name.' is already linked to byond '.$user->linkedByond->ckey.' but trying to link to '.$ckey;
        }

        DB::beginTransaction();

        $totals = [
            'users' => 0,
            'players' => 0,
            'player_claims' => 0,
            'discord_links' => 0,
            'byond_links' => 0,
        ];

        try {
            if (! $user) {
                // No user with the linked ckey or discord id exists
                $user = User::create([
                    'name' => $ckey,
                    'email' => Str::random(20).'@null.local',
                    'password' => Hash::make(Str::password()),
                    'passwordless' => true,
                    'emailless' => true,
                ]);
                $totals['users']++;
            }

            if (! $user->linkedDiscord) {
                // Link the user to the discord id
                $linkedDiscord = $this->linkToDiscord($user, $discordId);
                $totals['discord_links']++;
            }

            if (! $user->linkedByond) {
                // Link the user to the byond ckey
                $linkedByond = $this->linkToByond($user, $ckey);
                $totals['byond_links']++;
            }

            if (! $user->player) {
                if ($player && ! $player->user) {
                    // A player with the linked ckey exists but is not linked to a user
                    // Which means we can claim it
                    $totals['player_claims']++;
                } else {
                    $player = $this->createPlayer($ckey);
                    $totals['players']++;
                }

                $user->player_id = $player->id;
                $user->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'report' => [
                $ckey,
                $discordId,
                $user->name,
                $player->ckey,
                $linkedDiscord->discord_id,
                $linkedByond->ckey,
                implode('. ', $notes),
            ],
            'totals' => $totals,
        ];
    }
}
