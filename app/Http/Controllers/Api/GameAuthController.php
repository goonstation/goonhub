<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VerifyAuthResource;
use App\Models\User;
use App\Traits\ManagesPlayers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @tags Game Auth
 */
class GameAuthController extends Controller
{
    use ManagesPlayers;

    /**
     * Verify
     *
     * Verify a session token
     */
    public function verify(Request $request)
    {
        $data = $request->validate([
            'session' => 'required|string',
            'server_id' => 'required|string',
            'ip' => 'nullable|ipv4',
            'comp_id' => 'nullable|integer',
            'byond_major' => 'nullable|integer',
            'byond_minor' => 'nullable|integer',
            'round_id' => 'nullable|integer|exists:game_rounds,id',
        ]);

        $user = User::whereRememberToken($request->input('session'))->first();

        Log::channel('gameauth')->info('GameAuthController::verify', [
            'user' => $user,
            'data' => $data,
        ]);

        if (! $user) {
            return response()->json(['message' => 'Invalid session'], 401);
        }

        $this->loginPlayer($user->player, $data);

        return new VerifyAuthResource($user);
    }
}
