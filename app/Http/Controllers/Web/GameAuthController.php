<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\GameAuthController as ApiGameAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameAuth\RegisterRequest;
use App\Jobs\VerifyGameAuth;
use App\Models\Player;
use App\Models\User;
use App\Traits\ManagesPlayers;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class GameAuthController extends Controller
{
    use ManagesPlayers, ManagesUsers;

    public function showLogin()
    {
        return Inertia::render('Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $credentials['email'] = strtolower($credentials['email']);
        if (Auth::attempt($credentials, true)) {
            return to_route('game-auth.authed');
        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegister()
    {
        return Inertia::render('Register');
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

        return to_route('game-auth.authed');
    }

    public function showForgot()
    {
        return Inertia::render('Forgot', [
            'status' => session('status'),
        ]);
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
                return to_route('game-auth.show-login');
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

        $isGameAdmin = $user->isGameAdmin($cache['server_id']);

        if (! $isGameAdmin) {
            $this->handleTomatoSubscriber($user);
        }

        $data = [
            'player_id' => $user->player->id,
            'ckey' => $user->player->ckey,
            'key' => $user->player->key,
            'is_admin' => $isGameAdmin,
            'admin_rank' => $isGameAdmin ? $user->gameAdmin->rank->rank : null,
            'is_mentor' => $user->player->isMentor,
            'is_hos' => $user->player->isHos,
            'is_whitelisted' => $user->player->isWhitelistedOnServer($cache['server_id']),
            'can_bypass_cap' => $user->player->canBypassCapOnServer($cache['server_id']),
        ];

        VerifyGameAuth::dispatch($cache['server_id'], [
            'type' => 'auth_callback',
            'preauth_ckey' => $cache['ckey'],
            'data' => json_encode($data),
        ]);

        $cache['ckey'] = $data['ckey'];
        $cache['key'] = $data['key'];
        $this->loginPlayer($user->player, $cache);

        $request->session()->regenerate();

        return Inertia::render('Authed');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        if ($request->input('failed_login')) {
            return to_route('game-auth.show-login')
                ->withErrors(['Failed to login, please try again.']);
        }

        return Inertia::render('Logout', [
            'byondRef' => $request->input('ref') ?? null,
        ]);
    }

    public function discordRedirect()
    {
        /** @var \SocialiteProviders\Discord\Provider */
        $driver = Socialite::driver('discord');
        $driver = $driver->redirectUrl(route('game-auth.discord-callback'));

        return $driver->redirect();
    }

    public function discordCallback()
    {
        $user = null;
        try {
            $user = $this->handleDiscordCallback(route('game-auth.discord-callback'));
        } catch (ValidationException $e) {
            return to_route('game-auth.show-login')->withErrors($e->errors());
        } catch (\Throwable $e) {
            return to_route('game-auth.show-login')->withErrors([$e->getMessage()]);
        }

        Auth::login($user, true);

        return to_route('game-auth.authed');
    }

    public function showError()
    {
        return Inertia::render('Error');
    }
}
