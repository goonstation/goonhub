<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameServers\IndexRequest;
use App\Models\GameServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GameServersController extends Controller
{
    public function index(IndexRequest $request)
    {
        $gameServers = GameServer::with(['group:id,name']);
        if (! Auth::user()?->isGameAdmin()) {
            $gameServers->where('invisible', false);
        }

        return $gameServers->indexFilterPaginate(perPage: 30, sortBy: 'name', order: 'asc');
    }

    public function status(Request $request)
    {
        $request->validate([
            'server' => 'required|string',
        ]);

        $gameServer = GameServer::select(['server_id', 'address', 'port'])
            ->where('server_id', $request['server'])
            ->firstOrFail();

        if (Cache::missing("game_status_{$gameServer->server_id}")) {
            return ['error' => 'Server status not found'];
        }

        return Cache::get("game_status_{$gameServer->server_id}");
    }
}
