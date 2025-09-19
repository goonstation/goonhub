<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuildCancelRequest;
use App\Http\Requests\GameBuildCreateRequest;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\GameBuildResource;
use App\Http\Resources\GameBuildStatusResource;
use App\Models\GameBuild;
use App\Rules\DateRange;
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
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            /** @example main1 */
            'filters.server' => 'string',
            'filters.started_by' => 'string',
            'filters.branch' => 'string',
            'filters.commit' => 'string',
            'filters.map_id' => 'string',
            'filters.failed' => 'boolean',
            'filters.cancelled' => 'boolean',
            'filters.map_switch' => 'boolean',
            'filters.cancelled_by' => 'string',
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
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.ended_at' => new DateRange,
        ]);

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
    public function build(GameBuildCreateRequest $request)
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
    public function cancel(GameBuildCancelRequest $request)
    {
        $this->cancelBuild($request);

        return ['message' => 'Success'];
    }
}
