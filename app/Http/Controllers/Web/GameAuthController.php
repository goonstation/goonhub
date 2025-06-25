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
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cookie;
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

    public function showLogin()
    {
        return view('game-auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = strtolower($credentials['email']);
        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

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

        $player = new Player;
        $player->ckey = ckey($validated['name'].self::AUTH_SUFFIXES['goonhub']);
        $player->key = ckey($validated['name']).'-'.self::AUTH_SUFFIXES['goonhub'];
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

    public function authedDiscord()
    {
        if (! Auth::check()) {
            return redirect()->route('game-auth.show-login');
        }

        return view('game-auth.authed-discord');
    }

    public function logout()
    {
        Auth::logout();

        return view('game-auth.logout');
    }

    public function discordRedirect(string $state)
    {
        /** @var \SocialiteProviders\Discord\Provider */
        $socialite = Socialite::driver('discord-game-auth');

        Log::channel('gameauth')->info('GameAuthController::discordRedirect', [
            'state' => $state,
        ]);

        return $socialite->stateless()->with(['state' => $state])->redirect();
    }

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

    public function discordCallback(Request $request)
    {
        Log::channel('gameauth')->info('GameAuthController::discordCallback', [
            'request' => $request->all(),
        ]);

        $user = null;
        try {
            /** @var \SocialiteProviders\Discord\Provider */
            $socialite = Socialite::driver('discord-game-auth');
            $discordUser = $socialite->stateless()->user();
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
        $request->session()->regenerate();

        if ($request->input('state')) {
            $cookies = Cookie::getQueuedCookies();
            $rememberCookie = null;
            foreach ($cookies as $cookie) {
                if (str_starts_with($cookie->getName(), 'remember_web_')) {
                    $rememberCookie = $cookie;
                    break;
                }
            }

            /** @var \Illuminate\Contracts\Encryption\Encrypter */
            $encrypter = app('encrypter');
            $encryptedValue = $encrypter->encrypt(
                CookieValuePrefix::create($rememberCookie->getName(), $encrypter->getKey()).$rememberCookie->getValue(),
                EncryptCookies::serialized($rememberCookie->getName())
            );

            Log::channel('gameauth')->info('GameAuthController::discordCallback broadcast', [
                'channel' => 'discord-login.'.$request->input('state'),
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'session' => $user->getRememberToken(),
            ]);

            Broadcast::on('discord-login.'.$request->input('state'))
                ->as('DiscordLogin')
                ->with([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'session' => $user->getRememberToken(),
                    'cookie' => [
                        'name' => $rememberCookie->getName(),
                        'value' => $encryptedValue,
                        'domain' => $rememberCookie->getDomain(),
                        'path' => $rememberCookie->getPath(),
                        'expires' => $rememberCookie->getExpiresTime(),
                        'samesite' => $rememberCookie->getSameSite(),
                    ],
                ])
                ->sendNow();
        }

        return redirect()->route('game-auth.authed-discord');
    }
}
