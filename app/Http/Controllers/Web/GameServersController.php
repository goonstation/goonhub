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
        $gameServers = GameServer::query();
        if (! Auth::user()?->isGameAdmin()) {
            $gameServers->where('invisible', false);
        }

        return $gameServers->indexFilterPaginate(perPage: 30, sortBy: 'name', desc: false);
    }

    public function status(Request $request)
    {
        $request->validate([
            'server' => 'required|string',
        ]);

        $gameServer = GameServer::select(['server_id', 'address', 'port'])
            ->where('server_id', $request['server'])
            ->firstOrFail();

        $res = GameBridge::create()
            ->target($gameServer)
            ->message('status')
            ->send();

        if ($res->error) {
            return abort(500, $res->message);
        }

        parse_str($res->message, $status);

        return ['data' => $status];
    }
}
