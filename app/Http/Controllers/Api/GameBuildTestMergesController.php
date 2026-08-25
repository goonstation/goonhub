<?php

namespace App\Http\Controllers\Api;

use App\Attributes\HasDateRangeFilter;
use App\Attributes\HasServerFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuildTestMerges\IndexRequest;
use App\Http\Requests\GameBuildTestMerges\StoreRequest;
use App\Http\Requests\GameBuildTestMerges\UpdateRequest;
use App\Http\Resources\GameBuildTestMergeResource;
use App\Models\GameBuildTestMerge;
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
    #[
        HasServerFilter,
        HasDateRangeFilter(name: 'created_at'),
        HasDateRangeFilter(name: 'updated_at'),
    ]
    public function index(IndexRequest $request)
    {
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
    public function store(StoreRequest $request)
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
    public function update(UpdateRequest $request, GameBuildTestMerge $testMerge)
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
