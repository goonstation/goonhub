<?php

namespace App\Services\Auth;

use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;
use Laravel\Sanctum\Events\TokenAuthenticated;
use Laravel\Sanctum\Guard as SanctumGuard;

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
            $accessToken = PersonalAccessToken::findToken($token);
            /** @var \App\Models\User */
            $user = $accessToken?->tokenable;

            if (! $this->isValidAccessToken($accessToken) ||
                ! $this->supportsTokens($user)) {
                return;
            }

            $tokenable = $user->withAccessToken($accessToken);

            event(new TokenAuthenticated($accessToken));

            return $tokenable;
        }
    }
}
