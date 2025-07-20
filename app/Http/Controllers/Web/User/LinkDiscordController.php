<?php

namespace App\Http\Controllers\Web\User;

use App\Facades\DiscordApi;
use App\Http\Controllers\Controller;
use App\Models\DiscordSetting;
use App\Models\LinkedDiscordUser;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LinkDiscordController extends Controller
{
    public function redirect()
    {
        $user = Auth::user();

        if ($user->linkedDiscord) {
            return redirect()->route('profile.show')
                ->with('error', 'You are already linked to a Discord account.');
        }

        /** @var \SocialiteProviders\Discord\Provider */
        $driver = Socialite::driver('discord');
        $driver = $driver->redirectUrl(route('link-discord.callback'));

        return $driver->redirect();
    }

    public function callback()
    {
        /** @var \SocialiteProviders\Discord\Provider */
        $driver = Socialite::driver('discord');
        $discordUser = $driver->redirectUrl(route('link-discord.callback'))->user();
        $user = Auth::user();

        if ($user->linkedDiscord) {
            return redirect()->route('profile.show')
                ->with('error', 'You are already linked to a Discord account.');
        }

        $existingDiscordLink = LinkedDiscordUser::where('discord_id', $discordUser->getId())->first();
        if ($existingDiscordLink) {
            return redirect()->route('profile.show')
                ->with('error', 'This Discord account is already linked to another user.');
        }

        $user->linkedDiscord()->create([
            'discord_id' => $discordUser->getId(),
            'name' => $discordUser->getName(),
            'email' => $discordUser->getEmail(),
        ]);

        $grantDiscordRole = DiscordSetting::where('key', DiscordSetting::GRANT_ROLE_WHEN_LINKED)
            ->whereNotNull('value')
            ->first();

        if ($grantDiscordRole) {
            DiscordApi::guild()->addMemberRole(
                $discordUser->getId(),
                $grantDiscordRole->value,
                'Linked Goonhub account'
            );
        }

        return redirect()->route('profile.show')
            ->with('success', 'Successfully linked Discord account.');
    }

    public function unlink()
    {
        $user = Auth::user();

        if (! $user->linkedDiscord) {
            return redirect()->route('profile.show')
                ->with('error', 'You are not linked to a Discord account.');
        }

        $user->linkedDiscord()->delete();

        return redirect()->route('profile.show')
            ->with('success', 'Successfully unlinked Discord account.');
    }
}
