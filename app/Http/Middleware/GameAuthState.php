<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\GameAuthController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;

class GameAuthState
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $request->input('token'));

        $sessionToken = null;
        if (! $token) {
            $token = $request->session()->get('gameauth.token');
            $sessionToken = $token;
        }

        if (! $token || Cache::missing(GameAuthController::CACHE_PREFIX.$token)) {
            return Redirect::route('game-auth.error')->withErrors([
                'Unable to verify your authentication session, please try again.',
            ]);
        }

        if (! $request->session()->has('gameauth.token') || $sessionToken !== $token) {
            $request->session()->put('gameauth.token', $token);
        }

        return $next($request);
    }
}
