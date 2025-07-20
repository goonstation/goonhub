<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\FinesIndexRequest;
use App\Models\Events\EventFine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinesController extends Controller
{
    private function getFines()
    {
        return EventFine::select('id', 'round_id', 'amount', 'issuer', 'issuer_job', 'target', 'reason')
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

    public function index(FinesIndexRequest $request)
    {
        if ($this->wantsInertia()) {
            $this->setMeta(title: 'Fines', description: 'All fines');

            return Inertia::render('Events/Fines/Index', [
                'fines' => Inertia::lazy(fn () => $this->getFines()),
            ]);
        }

        return $this->getFines();
    }

    public function show(Request $request, int $fine)
    {
        $fine = EventFine::select(
            'id',
            'round_id',
            'amount',
            'issuer',
            'issuer_job',
            'target',
            'reason',
            'created_at'
        )
            ->where('id', $fine)
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
            title: 'Fine #'.number_format($fine->id),
            description: 'View detailed information of this fine',
            image: ['type' => 'fine', 'key' => $fine->id]
        );

        return Inertia::render('Events/Fines/Show', [
            'fine' => $fine,
        ]);
    }
}
