<?php

namespace App\Http\Controllers\Web;

use App\Facades\GameBridge;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameServers\IndexRequest;
use App\Models\GameServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $res = GameBridge::server($gameServer)->status();

        if ($res->failed()) {
            return abort(500, $res->getMessage());
        }

        return ['data' => $res->getData()];
    }
}
