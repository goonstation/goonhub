<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Medals\PlayersIndexRequest;
use App\Models\Medal;
use App\Models\Player;
use App\Models\PlayerMedal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Inertia;

class MedalsController extends Controller
{
    public function index(Request $request)
    {
        // Avoiding withCount as this is considerably faster
        $medals = Medal::select([
            'uuid',
            'title',
            'description',
            DB::raw('COALESCE(pm.earned_count, 0) AS earned_count'),
        ])
            ->where('hidden', false)
            ->joinSub(
                'SELECT medal_id, COUNT(*) AS earned_count FROM player_medals GROUP BY medal_id',
                'pm', 'medals.id', '=', 'pm.medal_id', 'left'
            )
            ->orderBy('title', 'asc')
            ->get();

        $recentMedalsEarned = PlayerMedal::select([
            'id',
            'player_id',
            'medal_id',
            'created_at',
        ])
            ->with([
                'player:id,ckey,key',
                'medal:id,uuid,title',
            ])
            ->whereRelation('medal', 'hidden', false)
            ->whereRelation('gameRound', 'ended_at', '!=', null)
            ->whereRelation('gameRound.server', 'invisible', false)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $this->setMeta(title: 'Medals', description: 'Check out all the medals that players can earn');

        return Inertia::render('Medals/Index', [
            'medals' => $medals,
            'recentMedalsEarned' => $recentMedalsEarned,
        ]);
    }

    public function show(Request $request, string $uuid)
    {
        $medal = Medal::select([
            'uuid',
            'title',
            'description',
            DB::raw('COALESCE(pm.earned_count, 0) AS earned_count'),
        ])
            ->where('uuid', $uuid)
            ->where('hidden', false)
            ->joinSub(
                'SELECT medal_id, COUNT(*) AS earned_count FROM player_medals GROUP BY medal_id',
                'pm', 'medals.id', '=', 'pm.medal_id', 'left'
            )
            ->firstOrFail();

        $this->setMeta(title: $medal->title, description: 'View details for this medal and who has earned it');

        return Inertia::render('Medals/Show', [
            'medal' => $medal,
        ]);
    }

    public function players(PlayersIndexRequest $request, string $uuid)
    {
        $medal = Medal::where('uuid', $uuid)
            ->where('hidden', false)
            ->firstOrFail();

        if (FacadesRequest::input('sort_by') === 'name') {
            FacadesRequest::merge(['sort_by' => 'ckey']);
        }

        return Player::select([
            'id',
            'ckey',
            'key',
            DB::raw('pm.created_at as earned_at'),
        ])
            ->joinSub(
                "SELECT player_id, created_at FROM player_medals WHERE medal_id = {$medal->id}",
                'pm', 'players.id', '=', 'pm.player_id', 'left'
            )
            ->whereRaw('pm.created_at IS NOT NULL')
            ->indexFilterPaginate(perPage: 30, sortBy: 'earned_at');
    }
}
