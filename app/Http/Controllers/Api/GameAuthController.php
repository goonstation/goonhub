<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeginAuthResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Str;

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
            'server_id' => 'required|string',
            'ckey' => 'required|string',
            'ip' => 'nullable|ipv4',
            'comp_id' => 'nullable|integer',
            'byond_major' => 'nullable|integer',
            'byond_minor' => 'nullable|integer',
            'round_id' => 'nullable|integer|exists:game_rounds,id',
        ]);

        $token = Str::random(32);
        $expiresAt = now()->addMinutes(5);
        Cache::put(self::CACHE_PREFIX.$token, $data, $expiresAt);
        Cache::put(self::CACHE_PREFIX_EXPIRES.$token, $expiresAt, $expiresAt);

        Log::channel('gameauth')->info('GameAuthController::begin', [
            'token' => $token,
            'data' => $data,
        ]);

        return new BeginAuthResource([
            'token' => $token,
        ]);
    }
}
