<?php

namespace App\Http\Controllers\Web;

use App\Facades\GameBridge;
use App\Http\Controllers\Api\GameAuthController as ApiGameAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameAuth\RegisterRequest;
use App\Models\Player;
use App\Models\User;
use App\Traits\ManagesPlayers;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class GameAuthController extends Controller
{
    use ManagesPlayers, ManagesUsers;

    public function showLogin(Request $request)
    {
        $token = $request->session()->get('gameauth.token');
        $cache = Cache::get(ApiGameAuthController::CACHE_PREFIX.$token);
        $discordRedirect = '';
        if (array_key_exists('legacy', $cache) && $cache['legacy']) {
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
            $user->setRelation('player', $player);
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

        // TODO: check for bridge error, retry

        $cache['ckey'] = $data['ckey'];
        $cache['key'] = $data['key'];
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

    public function discordRedirect()
    {
        /** @var \SocialiteProviders\Discord\Provider */
        $driver = Socialite::driver('discord');
        $driver = $driver->redirectUrl(route('game-auth.discord-callback'));

        return $driver->redirect();
    }

    public function discordCallback(Request $request)
    {
        Log::channel('gameauth')->info('GameAuthController::discordCallback', [
            'request' => $request->all(),
        ]);

        $user = null;
        try {
            $user = $this->handleDiscordCallback(route('game-auth.discord-callback'));
        } catch (ValidationException $e) {
            return redirect()->route('game-auth.show-login')->withErrors($e->errors());
        } catch (\Throwable $e) {
            return redirect()->route('game-auth.show-login')->withErrors([$e->getMessage()]);
        }

        Auth::login($user, true);

        $token = $request->session()->get('gameauth.token');
        $cache = Cache::get(ApiGameAuthController::CACHE_PREFIX.$token);
        if (array_key_exists('legacy', $cache) && $cache['legacy']) {
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

            // TODO: check for bridge error, retry

            return redirect()->route('game-auth.authed-discord');
        }

        return redirect()->route('game-auth.authed');
    }

    public function showError()
    {
        return view('game-auth.error');
    }
}
