<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeginAuthResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @tags Game Auth
 */
class GameAuthController extends Controller
{
    const CACHE_PREFIX = 'game_auth_state_';

    const CACHE_PREFIX_EXPIRES = 'game_auth_state_expires_';

    public function begin(Request $request)
    {
        $data = $request->validate([
            'timeout' => 'required|integer', // seconds
            'server_id' => 'required|string',
            'ckey' => 'required|string',
            'ip' => 'nullable|ipv4',
            'comp_id' => 'nullable|integer',
            'byond_major' => 'nullable|integer',
            'byond_minor' => 'nullable|integer',
            'round_id' => 'nullable|integer|exists:game_rounds,id',
        ]);

        $token = Str::random(32);

        // Ensure the timeout is 24 hours or less
        $timeout = max($data['timeout'], 86400);
        if ($timeout <= 0) {
            // Someone turned "off" timeouts on the game code side
            // Set it to 24 hours so cache items don't stay forever
            $timeout = 86400;
        }

        $expiresAt = now()->addSeconds($timeout);
        Cache::put(self::CACHE_PREFIX.$token, $data, $expiresAt);
        Cache::put(self::CACHE_PREFIX_EXPIRES.$token, $expiresAt, $expiresAt);

        return new BeginAuthResource([
            'token' => $token,
        ]);
    }
}
