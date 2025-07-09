<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Traits\ManagesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ManagesUsers;

    public function discordRedirect()
    {
        /** @var \SocialiteProviders\Discord\Provider */
        $driver = Socialite::driver('discord');
        $driver->redirectUrl(route('auth.discord-callback'));

        return $driver->redirect();
    }

    public function discordCallback()
    {
        $user = null;

        try {
            $user = $this->handleDiscordCallback();
        } catch (ValidationException $e) {
            return redirect()->route('login')->withErrors($e->errors());
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([$e->getMessage()]);
        }

        Auth::login($user, true);

        return redirect(RouteServiceProvider::HOME);
    }
}
