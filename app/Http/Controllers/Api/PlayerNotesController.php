<?php

namespace App\Http\Controllers\Api;

use App\Attributes\HasDateRangeFilter;
use App\Attributes\HasServerFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerNotes\IndexRequest;
use App\Http\Requests\PlayerNotes\StoreRequest;
use App\Http\Resources\PlayerNoteResource;
use App\Models\Player;
use App\Models\PlayerNote;
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
    #[
        HasServerFilter,
        HasDateRangeFilter(name: 'created_at'),
        HasDateRangeFilter(name: 'updated_at'),
    ]
    public function index(IndexRequest $request)
    {
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
