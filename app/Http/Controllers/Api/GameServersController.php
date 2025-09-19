<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameServerResource;
use App\Models\GameServer;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

#[Group('Game Servers')]
class GameServersController extends Controller
{
    /**
     * List
     *
     * List all servers
     *
     * @unauthenticated
     *
     * @return AnonymousResourceCollection<GameServerResource>
     */
    public function index()
    {
        return Cache::remember('game_servers', now()->addSeconds(30), function () {
            return GameServerResource::collection(
                GameServer::with([
                    'currentPlayersOnline',
                    'currentRound.mapRecord',
                    'gameBuildSetting.map',
                ])->get()
            );
        });
    }
}
