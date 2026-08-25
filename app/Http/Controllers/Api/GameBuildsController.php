<?php

namespace App\Http\Controllers\Api;

use App\Attributes\HasDateRangeFilter;
use App\Attributes\HasServerFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuilds\CancelRequest;
use App\Http\Requests\GameBuilds\IndexRequest;
use App\Http\Requests\GameBuilds\StoreRequest;
use App\Http\Resources\GameBuildResource;
use App\Http\Resources\GameBuildStatusResource;
use App\Models\GameBuild;
use App\Traits\ManagesGameBuilds;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

#[Group('Game Builds')]
class GameBuildsController extends Controller
{
    use ManagesGameBuilds;

    /**
     * List
     *
     * List filtered and paginated bans
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<GameBuildResource>>
     */
    #[
        HasServerFilter,
        HasDateRangeFilter(name: 'created_at'),
        HasDateRangeFilter(name: 'updated_at'),
        HasDateRangeFilter(name: 'ended_at'),
    ]
    public function index(IndexRequest $request)
    {
        return GameBuildResource::collection(
            GameBuild::indexFilterPaginate()
        );
    }

    /**
     * Status
     *
     * Get the current status of game builds in process or queued
     */
    public function status()
    {
        $status = $this->getStatus();

        return new GameBuildStatusResource($status);
    }

    /**
     * Build
     *
     * Run a game build
     */
    public function build(StoreRequest $request)
    {
        try {
            $this->addBuild($request);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return ['message' => 'Success'];
    }

    /**
     * Cancel
     *
     * Cancel a build
     */
    public function cancel(CancelRequest $request)
    {
        $this->cancelBuild($request);

        return ['message' => 'Success'];
    }
}
