<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameAuth\RegisterRequest;
use App\Libraries\DiscordBot;
use App\Models\Player;
use App\Models\PlayerHos;
use App\Models\PlayerMentor;
use App\Models\User;
use App\Traits\ManagesPlayers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class GameAuthController extends Controller
{
    use ManagesPlayers;

    public function showLogin()
    {
        return view('game-auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt([
            'password' => $credentials['password'],
            function (Builder $query) use ($credentials) {
                return $query->whereRelation('player', 'ckey', '=', ckey($credentials['name']));
            },
        ], true)) {
            $request->session()->regenerate();

            return redirect()->route('game-auth.authed');
        }

        return back()->withErrors([
            'name' => 'The provided credentials do not match our records.',
        ])->onlyInput('name');
    }

    public function showRegister()
    {
        return view('game-auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $player = new Player;
        $player->ckey = $validated['ckey'];
        $player->key = $validated['name'];
        $player->save();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'player_id' => $player->id,
        ]);

        Auth::login($user, true);

        return redirect()->route('game-auth.authed');
    }

    public function showForgot()
    {
        return view('game-auth.forgot');
    }

    public function authed()
    {
        if (! Auth::check()) {
            return redirect()->route('game-auth.show-login');
        }

        return view('game-auth.authed');
    }

    public function logout()
    {
        Auth::logout();

        return view('game-auth.logout');
    }

    public function discordRedirect()
    {
        return Socialite::driver('discord-game-auth')->redirect();
    }

    /**
     * Create a player with a given key, or with a unique suffix if the key is already taken.
     */
    private function createPlayer(string $key): Player
    {
        $attempts = 0;
        $canCreatePlayer = false;
        $keyToCreate = $key;
        while (! $canCreatePlayer && $attempts < 10) {
            if (Player::where('ckey', ckey($keyToCreate))->exists()) {
                $keyToCreate = $key.'-'.rand(10000, 99999);
            } else {
                $canCreatePlayer = true;
            }
            $attempts++;
        }

        if (! $canCreatePlayer) {
            throw new \Exception('Failed to create player');
        }

        return Player::withoutAuditing(function () use ($keyToCreate) {
            return Player::create(['ckey' => ckey($keyToCreate), 'key' => $keyToCreate]);
        });
    }

    public function discordCallback()
    {
        $user = null;
        try {
            // $discordUser = Socialite::driver('discord-game-auth')->user();
            // $discordId = $discordUser->getId();
            // $discordDetails = DiscordBot::export('goonhub/auth', 'GET', [
            //     'discord_id' => $discordId,
            // ]);

            // $discordName = $discordUser->getName();
            // $discordEmail = $discordUser->getEmail();

            // Debug
            // $discordId = '145630418656428032';
            $counter = 17;
            $discordId = $counter;
            $discordDetails = [
                'ckey' => 'new'.$counter,
                'is_admin' => false,
                'is_mentor' => true,
                'is_hos' => true,
                'is_player' => true,
            ];
            // $discordDetails = [];
            $discordName = 'new'.$counter;
            $discordEmail = 'new'.$counter.'@null.local';
            // Debug end

            $user = User::where('discord_id', $discordId)->first();

            if (! $user) {
                // Registering

                if (! $discordEmail || ($discordEmail && User::where('email', $discordEmail)->exists())) {
                    $discordEmail = Str::random(20).'@null.local';
                }

                $user = User::create([
                    'name' => $discordName,
                    'email' => $discordEmail,
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
                    $player = $this->createPlayer($discordName);
                }
                $user->player_id = $player->id;
                $user->save();
            }

            // Assign special abilities based on Discord roles
            if ($discordDetails['is_mentor']) {
                PlayerMentor::createOrFirst(['player_id' => $user->player_id]);
            }
            if ($discordDetails['is_hos']) {
                PlayerHos::createOrFirst(['player_id' => $user->player_id]);
            }

        } catch (ValidationException $e) {
            return redirect()->route('game-auth.show-login')->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->route('game-auth.show-login')->withErrors([$e->getMessage()]);
        }

        $user->load('player');

        return redirect()->route('game-auth.show-login')->with('success', 'Would log in as '.$user->name.' ('.$user->id.') with player '.$user->player->ckey.' ('.$user->player->id.')');

        // Auth::login($user, true);

        // return redirect()->route('game-auth.authed');
    }
}
