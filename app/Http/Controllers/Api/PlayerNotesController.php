<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Requests\PlayerNotes\StoreRequest;
use App\Http\Resources\PlayerNoteResource;
use App\Models\Player;
use App\Models\PlayerNote;
use App\Rules\DateRange;
use App\Traits\ManagesPlayerNotes;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

#[Group('Player Notes')]
class PlayerNotesController extends Controller
{
    use ManagesPlayerNotes;

    /**
     * List
     *
     * List paginated and filtered player notes
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<PlayerNoteResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.ckey' => 'string',
            'filters.game_admin' => 'string',
            'filters.server' => 'string',
            'filters.round' => 'int',
            'filters.note' => 'string',
            /** Enable exact matching on the ckey filter */
            'filters.exact' => 'boolean',
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.created_at' => new DateRange,
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.updated_at' => new DateRange,
        ]);

        return PlayerNoteResource::collection(
            PlayerNote::with(['player', 'gameAdmin.player'])
                ->indexFilterPaginate()
        );
    }

    /**
     * Add
     *
     * Add a new player note
     */
    public function store(StoreRequest $request)
    {
        return $this->addNote($request);
    }

    /**
     * Update
     *
     * Update an existing player note
     */
    public function update(StoreRequest $request, PlayerNote $note)
    {
        return $this->updateNote($request, $note);
    }

    /**
     * Delete
     *
     * Delete an existing player note
     */
    public function destroy(PlayerNote $note)
    {
        $note->delete();

        return ['message' => 'Note removed'];
    }
}
