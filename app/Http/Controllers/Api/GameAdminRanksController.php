<?php

namespace App\Http\Controllers\Api;

use App\Attributes\HasDateRangeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameAdminRanks\IndexRequest;
use App\Http\Resources\GameAdminRankResource;
use App\Models\GameAdminRank;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

#[Group('Game Admin Ranks')]
class GameAdminRanksController extends Controller
{
    /**
     * List
     *
     * List paginated and filtered game admin ranks
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<GameAdminRankResource>>
     */
    #[
        HasDateRangeFilter(name: 'created_at'),
        HasDateRangeFilter(name: 'updated_at'),
    ]
    public function index(IndexRequest $request)
    {
        return GameAdminRankResource::collection(
            GameAdminRank::indexFilterPaginate()
        );
    }

    /**
     * Add
     *
     * Add a new game admin rank
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'rank' => 'required|string',
        ]);

        $gameAdminRank = new GameAdminRank;
        $gameAdminRank->rank = $data['rank'];
        $gameAdminRank->save();

        return new GameAdminRankResource($gameAdminRank);
    }

    /**
     * Update
     *
     * Update an existing game admin rank
     */
    public function update(Request $request, GameAdminRank $gameAdminRank)
    {
        $data = $request->validate([
            'rank' => 'required|string',
        ]);

        $gameAdminRank->rank = $data['rank'];
        $gameAdminRank->save();

        return new GameAdminRankResource($gameAdminRank);
    }

    /**
     * Delete
     *
     * Delete an existing game admin rank
     */
    public function destroy(GameAdminRank $gameAdminRank)
    {
        $gameAdminRank->delete();

        return ['message' => 'Game admin rank deleted'];
    }
}
