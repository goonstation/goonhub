<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\TicketsIndexRequest;
use App\Models\Events\EventTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketsController extends Controller
{
    private function getTickets()
    {
        return EventTicket::select('id', 'round_id', 'issuer', 'issuer_job', 'reason', 'target')
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

    public function index(TicketsIndexRequest $request)
    {
        if ($this->wantsInertia()) {
            $this->setMeta(title: 'Tickets', description: 'All tickets');

            return Inertia::render('Events/Tickets/Index', [
                'tickets' => Inertia::lazy(fn () => $this->getTickets()),
            ]);
        }

        return $this->getTickets();
    }

    public function show(Request $request, int $ticket)
    {
        $ticket = EventTicket::select(
            'id',
            'round_id',
            'issuer',
            'issuer_job',
            'reason',
            'target',
            'created_at'
        )
            ->where('id', $ticket)
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
            title: 'Ticket #'.number_format($ticket->id),
            description: 'View detailed information of this ticket',
            image: ['type' => 'ticket', 'key' => $ticket->id]
        );

        return Inertia::render('Events/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }
}
