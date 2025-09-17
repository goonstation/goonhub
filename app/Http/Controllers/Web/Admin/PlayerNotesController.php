<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerNotes\StoreRequest;
use App\Models\PlayerNote;
use App\Traits\ManagesPlayerNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PlayerNotesController extends Controller
{
    use ManagesPlayerNotes;

    public function index(Request $request)
    {
        $playerNotes = PlayerNote::with([
            'player:id,ckey',
            'gameAdmin.player',
            'gameServer:id,server_id,short_name',
        ])->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/PlayerNotes/Index', [
                'playerNotes' => $playerNotes,
            ]);
        } else {
            return $playerNotes;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/PlayerNotes/Create');
    }

    public function store(StoreRequest $request)
    {
        $request->merge([
            'game_admin_id' => $request->user()->gameAdmin->id,
        ]);
        $note = $this->addNote($request);
        $note->load(['gameAdmin.player', 'gameServer']);

        if ($request->has('return_note')) {
            return $note;
        } else {
            return to_route('admin.notes.index');
        }
    }

    public function edit(PlayerNote $note)
    {
        $note->load('player');

        return Inertia::render('Admin/PlayerNotes/Edit', [
            'note' => $note,
        ]);
    }

    public function update(StoreRequest $request, PlayerNote $note)
    {
        try {
            $request = $request->merge([
                'game_admin_id' => $request->user()->gameAdmin->id,
            ]);
            $this->updateNote($request, $note);
        } catch (\Exception $e) {
            return Redirect::back()->withErrors(['error' => $e->getMessage()]);
        }

        return to_route('admin.notes.index');
    }

    public function show(int $note)
    {
        $note = PlayerNote::with([
            'player:id,ckey',
            'gameAdmin.player',
            'gameServer:id,server_id,short_name',
        ])
            ->findOrFail($note);

        return Inertia::render('Admin/PlayerNotes/Show', [
            'note' => $note,
        ]);
    }

    public function destroy(PlayerNote $note)
    {
        $note->delete();

        return ['message' => 'Note removed'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $this->validate($request, [
            'ids' => 'required|array',
        ]);

        $notes = PlayerNote::whereIn('id', $data['ids']);
        $notes->delete();

        return ['message' => 'Notes removed'];
    }
}
