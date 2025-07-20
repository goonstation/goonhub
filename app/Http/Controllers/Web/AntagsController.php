<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\AntagsIndexRequest;
use App\Models\Events\EventAntag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AntagsController extends Controller
{
    private function getAntags()
    {
        return EventAntag::with([
            'objectives:id,round_id,player_id,success',
        ])
            ->select(
                'id',
                'round_id',
                'player_id',
                'mob_job',
                'mob_name',
                'success',
                'traitor_type'
            )
            ->whereRelation('gameRound', 'ended_at', '!=', null)
            ->whereRelation('gameRound.server', 'invisible', false)
            ->indexFilterPaginate(perPage: 20);
    }

    public function index(AntagsIndexRequest $request)
    {
        if ($this->wantsInertia()) {
            $this->setMeta(title: 'Antagonists', description: 'All antagonists');

            return Inertia::render('Events/Antags/Index', [
                'antags' => Inertia::lazy(fn () => $this->getAntags()),
            ]);
        }

        return $this->getAntags();
    }

    public function show(Request $request, int $antag)
    {
        $antag = EventAntag::with([
            'objectives:id,round_id,player_id,objective,success',
            'itemPurchases:id,round_id,player_id,item',
        ])
            ->select(
                'id',
                'round_id',
                'player_id',
                'mob_job',
                'mob_name',
                'success',
                'traitor_type'
            )
            ->where('id', $antag)
            ->whereRelation('gameRound', 'ended_at', '!=', null)
            ->whereRelation('gameRound.server', 'invisible', false)
            ->firstOrFail();

        $this->setMeta(
            title: 'Antagonist #'.number_format($antag->id),
            description: 'View detailed information of this antagonist',
            image: ['type' => 'antag', 'key' => $antag->id]
        );

        return Inertia::render('Events/Antags/Show', [
            'antag' => $antag,
        ]);
    }
}
