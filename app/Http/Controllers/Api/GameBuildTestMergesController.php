<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuildTestMergeCreateRequest;
use App\Http\Requests\GameBuildTestMergeUpdateRequest;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\GameBuildTestMergeResource;
use App\Models\GameBuildTestMerge;
use App\Rules\DateRange;
use App\Traits\ManagesGameBuildTestMerges;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

#[Group('Game Build Test Merges')]
class GameBuildTestMergesController extends Controller
{
    use ManagesGameBuildTestMerges;

    /**
     * List
     *
     * List paginated and filtered game build test merges
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<GameBuildTestMergeResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.pr' => 'int',
            'filters.server' => 'string',
            'filters.added_by' => 'string',
            'filters.updated_by' => 'string',
            'filters.commit' => 'string',
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

        return GameBuildTestMergeResource::collection(
            GameBuildTestMerge::with(['buildSettings', 'addedBy', 'updatedBy'])
                ->indexFilterPaginate()
        );
    }

    /**
     * Add
     *
     * Add one or multiple new game build test merges
     */
    public function store(GameBuildTestMergeCreateRequest $request)
    {
        try {
            return $this->addTestMerge($request);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update
     *
     * Update an existing game build test merge
     */
    public function update(GameBuildTestMergeUpdateRequest $request, GameBuildTestMerge $testMerge)
    {
        try {
            return $this->updateTestMerge($request, $testMerge);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete
     *
     * Delete an existing game build test merge
     */
    public function destroy(GameBuildTestMerge $testMerge)
    {
        $this->destroyTestMerge($testMerge);

        return ['message' => 'Test merge removed'];
    }
}
