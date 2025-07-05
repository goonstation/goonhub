<?php

namespace App\Http\Controllers\Web;

use App\Facades\GameBridge;
use App\Http\Controllers\Api\GameAuthController as ApiGameAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameAuth\RegisterRequest;
use App\Libraries\DiscordBot;
use App\Models\Player;
use App\Models\PlayerHos;
use App\Models\PlayerMentor;
use App\Models\User;
use App\Traits\ManagesPlayers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class GameAuthController extends Controller
{
    use ManagesPlayers;

    const AUTH_SUFFIXES = [
        'byond' => '',
        'goonhub' => 'G',
        'discord' => 'D',
    ];

    /**
     * Create a player with a given key, or with a unique suffix if the key is already taken.
     */
    private function createPlayer(string $key, string $suffixKey = 'byond'): Player
    {
        $attempts = 0;
        $canCreatePlayer = false;
        $key = self::AUTH_SUFFIXES[$suffixKey] ? $key.'-'.self::AUTH_SUFFIXES[$suffixKey] : $key;
        $keyToCreate = $key;
        while (! $canCreatePlayer && $attempts < 10) {
            if (Player::where('ckey', ckey($keyToCreate))->exists()) {
                $keyToCreate = $key.rand(10000, 99999);
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

    public function showLogin(Request $request)
    {
        $legacy = $request->input('legacy') ?? false;
        $discordRedirect = '';
        if ($legacy) {
            $token = $request->session()->get('gameauth.token');
            $discordRedirect = 'byond://winset?command=.openlink "'.
                urlencode(route('game-auth.discord-redirect', ['token' => $token, 'legacy' => true])).
                '"';
        } else {
            $discordRedirect = route('game-auth.discord-redirect');
        }

        return view('game-auth.login', [
            'discordRedirect' => $discordRedirect,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = strtolower($credentials['email']);
        if (Auth::attempt($credentials, true)) {
            return redirect()->route('game-auth.authed');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('game-auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $player = $this->createPlayer($validated['name'], 'goonhub');

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

    public function authed(Request $request)
    {
        $token = $request->session()->get('gameauth.token');
        $cache = Cache::pull(ApiGameAuthController::CACHE_PREFIX.$token);

        if (array_key_exists('user_id', $cache)) {
            $user = User::findOrFail($cache['user_id']);
            Auth::login($user, true);
        } else {
            if (! Auth::check()) {
                return redirect()->route('game-auth.show-login');
            }

            $user = Auth::user();
        }

        // Users that don't have a player registered outside of the game auth flow
        if (! $user->player) {
            $player = $this->createPlayer($user->name, 'goonhub');
            $user->player_id = $player->id;
            $user->save();
        }

        $data = [
            'player_id' => $user->player->id,
            'ckey' => $user->player->ckey,
            'key' => $user->player->key,
            'is_admin' => $user->isGameAdmin(),
            'admin_rank' => $user->isGameAdmin() ? $user->gameAdmin->rank->rank : null,
            'is_mentor' => $user->player->isMentor,
            'is_hos' => $user->player->isHos,
            'is_whitelisted' => $user->player->isWhitelisted($cache['server_id']),
            'can_bypass_cap' => $user->player->canBypassCap($cache['server_id']),
        ];

        GameBridge::create()
            ->target($cache['server_id'])
            ->message([
                'type' => 'auth_callback',
                'preauth_ckey' => $cache['ckey'],
                'data' => json_encode($data),
            ])
            ->force(true)
            ->sendAndForget();

        $this->loginPlayer($user->player, $cache);

        $request->session()->regenerate();

        return view('game-auth.authed');
    }

    public function authedDiscord()
    {
        if (! Auth::check()) {
            return redirect()->route('game-auth.show-login');
        }

        return view('game-auth.authed-discord');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        if ($request->input('failed_login')) {
            return redirect()->route('game-auth.show-login')
                ->withErrors(['Failed to login, please try again.']);
        }

        return view('game-auth.logout', [
            'ref' => $request->input('ref') ?? null,
        ]);
    }

    public function discordRedirect(Request $request)
    {
        $request->session()->put('gameauth.legacy', $request->input('legacy', false));

        return Socialite::driver('discord-game-auth')->redirect();
    }

    public function discordCallback(Request $request)
    {
        Log::channel('gameauth')->info('GameAuthController::discordCallback', [
            'request' => $request->all(),
        ]);

        $user = null;
        try {
            $discordUser = Socialite::driver('discord-game-auth')->user();
            $discordId = $discordUser->getId();
            $discordDetails = DiscordBot::export('goonhub/auth', 'GET', [
                'discord_id' => $discordId,
            ]);
            $discordName = $discordUser->getName();
            $discordEmail = $discordUser->getEmail();

            // Debug
            // $discordId = '145630418656428032';
            // $counter = 17;
            // $discordId = $counter;
            // $discordDetails = [
            //     'ckey' => 'new'.$counter,
            //     'is_admin' => false,
            //     'is_mentor' => true,
            //     'is_hos' => true,
            //     'is_player' => true,
            // ];
            // $discordDetails = [];
            // $discordName = 'wirewraith4';
            // $discordEmail = 'wirewraith4@gmail.com';
            // Debug end

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

        } catch (ValidationException $e) {
            return redirect()->route('game-auth.show-login')->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->route('game-auth.show-login')->withErrors([$e->getMessage()]);
        }

        Auth::login($user, true);

        $legacy = $request->session()->get('gameauth.legacy', false);
        if ($legacy) {
            $token = $request->session()->get('gameauth.token');
            $cache = Cache::get(ApiGameAuthController::CACHE_PREFIX.$token);
            $expiresAt = Cache::get(ApiGameAuthController::CACHE_PREFIX_EXPIRES.$token);

            $cache['user_id'] = $user->id;
            Cache::put(ApiGameAuthController::CACHE_PREFIX.$token, $cache, $expiresAt);

            GameBridge::create()
                ->target($cache['server_id'])
                ->message([
                    'type' => 'legacy_discord_auth_callback',
                    'preauth_ckey' => $cache['ckey'],
                ])
                ->force(true)
                ->sendAndForget();

            return redirect()->route('game-auth.authed-discord');
        }

        return redirect()->route('game-auth.authed');
    }

    public function showError()
    {
        return view('game-auth.error');
    }
}
