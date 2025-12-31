<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameRounds\IndexRequest;
use App\Models\Events\EventLog;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogsController extends Controller
{
    public function index(IndexRequest $request)
    {
        return Inertia::render('Admin/Logs/Index', [
            // Use simple pagination to avoid expensive COUNT query on large tables.
            // The has('logs') EXISTS subquery combined with LengthAwarePaginator's COUNT
            // causes severe performance issues with millions of events_logs records.
            'rounds' => Inertia::lazy(fn () => GameRound::with([
                'server:server_id,name,short_name',
            ])
                ->withCount('logs')
                ->has('logs')
                ->indexFilterPaginate(perPage: 30, simple: true)),
        ]);
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
