<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\DeathsIndexRequest;
use App\Models\Events\EventDeath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DeathsController extends Controller
{
    private function getDeaths()
    {
        return EventDeath::select(
            'id',
            'round_id',
            'bruteloss',
            'fireloss',
            'gibbed',
            'last_words',
            'mob_job',
            'mob_name',
            'oxyloss',
            'toxloss'
        )
            ->withSum([
                'votes as votes' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(value), 0)'));
                },
            ], 'value')
            ->with('userVotes:voteable_id,value')
            ->whereRelation('gameRound', 'ended_at', '!=', null)
            ->whereRelation('gameRound.server', 'invisible', false)
            ->indexFilterPaginate(perPage: 20);
    }

    public function index(DeathsIndexRequest $request)
    {
        if ($this->wantsInertia()) {
            $this->setMeta(title: 'Deaths', description: 'All deaths');

            return Inertia::render('Events/Deaths/Index', [
                'deaths' => Inertia::lazy(fn () => $this->getDeaths()),
            ]);
        }

        return $this->getDeaths();
    }

    public function show(Request $request, int $death)
    {
        $death = EventDeath::select(
            'id',
            'round_id',
            'bruteloss',
            'fireloss',
            'gibbed',
            'last_words',
            'mob_job',
            'mob_name',
            'oxyloss',
            'toxloss'
        )
            ->where('id', $death)
            ->withSum([
                'votes as votes' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(value), 0)'));
                },
            ], 'value')
            ->with('userVotes:voteable_id,value')
            ->whereRelation('gameRound', 'ended_at', '!=', null)
            ->whereRelation('gameRound.server', 'invisible', false)
            ->firstOrFail();

        $this->setMeta(
            title: 'Death #'.number_format($death->id),
            description: 'View detailed information of this death',
            image: ['type' => 'death', 'key' => $death->id]
        );

        return Inertia::render('Events/Deaths/Show', [
            'death' => $death,
        ]);
    }
}
