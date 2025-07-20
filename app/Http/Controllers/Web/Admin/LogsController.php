<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\EventLog;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $rounds = GameRound::with([
            'server:server_id,name,short_name',
        ])
            ->withCount('logs')
            ->has('logs')
            ->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Logs/Index', [
                'rounds' => $rounds,
            ]);
        } else {
            return $rounds;
        }
    }

    public function show(Request $request, GameRound $gameRound)
    {
        $gameRound->load([
            'server:server_id,name',
            'latestStationName:id,round_id,name',
            'mapRecord:id,map_id,name',
        ]);

        return Inertia::render('Admin/Logs/Show', [
            'round' => $gameRound,
        ]);
    }

    public function getLogs(GameRound $gameRound)
    {
        return EventLog::where('round_id', $gameRound->id)->orderBy('created_at', 'asc')->get();
    }
}
