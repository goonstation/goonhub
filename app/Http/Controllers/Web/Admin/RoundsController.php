<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoundsController extends Controller
{
    public function index(Request $request)
    {
        $rounds = GameRound::with(['server', 'mapRecord'])->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Rounds/Index', [
                'rounds' => $rounds,
            ]);
        } else {
            return $rounds;
        }
    }

    public function show(Request $request, GameRound $round)
    {
        $round->load([
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
        ])
            ->loadCount([
                'beeSpawns',
            ]);

        return Inertia::render('Admin/Rounds/Show', [
            'round' => $round,
        ]);
    }
}
