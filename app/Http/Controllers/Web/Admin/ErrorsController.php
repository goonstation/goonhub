<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\ErrorPermissions;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Models\Events\EventError;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;

class ErrorsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasPermission::using(ErrorPermissions::VIEW),
        ];
    }

    public function index(Request $request)
    {
        $rounds = GameRound::with([
            'server:server_id,name,short_name',
        ])
            ->withCount('errors')
            ->has('errors')
            ->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Errors/Index', [
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

        return Inertia::render('Admin/Errors/Show', [
            'round' => $gameRound,
        ]);
    }

    public function getErrors(GameRound $gameRound)
    {
        return EventError::where('round_id', $gameRound->id)->orderBy('created_at', 'asc')->get();
    }

    public function summary(Request $request)
    {
        return Inertia::render('Admin/Errors/Summary');
    }
}
