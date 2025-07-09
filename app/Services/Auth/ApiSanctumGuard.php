<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Laravel\Sanctum\Events\TokenAuthenticated;
use Laravel\Sanctum\Guard as SanctumGuard;
use Laravel\Sanctum\Sanctum;

class ApiSanctumGuard extends SanctumGuard
{
    /**
     * Override to avoid:
     *  - Checking 'web' guard first (or whatever other guards are in sanctum.php)
     *  - Setting modified fields of PAT
     */
    public function __invoke(Request $request)
    {
        if ($token = $this->getTokenFromRequest($request)) {
            /** @var \Laravel\Sanctum\PersonalAccessToken */
            $model = Sanctum::$personalAccessTokenModel;
            $accessToken = $model::findToken($token);
            /** @var \App\Models\User */
            $user = $accessToken?->tokenable;

            if (! $this->isValidAccessToken($accessToken) ||
                ! $this->supportsTokens($user)) {
                return;
            }

            /** @var \App\Models\User */
            $tokenable = $user->withAccessToken($accessToken);

            event(new TokenAuthenticated($accessToken));

            return $tokenable;
        }
    }
}
