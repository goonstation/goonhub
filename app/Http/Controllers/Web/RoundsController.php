<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameRounds\IndexRequest;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoundsController extends Controller
{
    private function getRounds()
    {
        return GameRound::with([
            'server:server_id,name',
            'mapRecord:id,map_id,name',
            'latestStationName:id,round_id,name',
        ])
            ->where('ended_at', '!=', null)
            ->whereRelation('server', 'invisible', '!=', true)
            ->indexFilterPaginate(perPage: 30);
    }

    public function index(IndexRequest $request)
    {
        if ($this->wantsInertia()) {
            $this->setMeta(
                title: 'Rounds',
                description: 'Search through all the game rounds that have ever happened'
            );

            return Inertia::render('Rounds/Index', [
                'rounds' => Inertia::lazy(fn () => $this->getRounds()),
            ]);
        }

        return $this->getRounds();
    }

    public function show(Request $request, int $round)
    {
        $gameRound = GameRound::with([
            'server:server_id,name',
            'latestStationName:id,round_id,name',
            'aiLaws:id,round_id,ai_name,law_number,law_text,uploader_name',
            'deaths:id,round_id,mob_name,mob_job,bruteloss,fireloss,toxloss,oxyloss,gibbed,created_at',
            'fines:id,round_id,issuer,target,reason,amount',
            'tickets:id,round_id,issuer,target,reason',
            'antags:id,round_id,player_id,mob_name,mob_job,traitor_type,success',
            'antagObjectives:id,round_id,player_id,objective,success',
            'antagItemPurchases:id,round_id,player_id,item,cost',
            'participations:id,round_id,created_at',
            'mapRecord:id,map_id,name',
        ])->withCount([
            'beeSpawns',
        ])
            ->where('ended_at', '!=', null)
            ->whereRelation('server', 'invisible', '!=', true)
            ->findOrFail($round);

        $this->setMeta(
            title: 'Round #'.number_format($gameRound->id),
            description: 'See everything that happened on round #'.number_format($gameRound->id),
            image: ['type' => 'round', 'key' => $gameRound->id]
        );

        return Inertia::render('Rounds/Show', [
            'round' => $gameRound,
        ]);
    }
}
