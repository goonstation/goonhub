<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\GameServerResource;
use App\Models\GameServer;
use App\Rules\DateRange;
use App\Traits\IndexableQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @tags Game Servers
 */
class GameServersController extends Controller
{
    use IndexableQuery;

    /**
     * List
     *
     * List filtered and paginated servers
     *
     * @unauthenticated
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<GameServerResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            /** @example main1 */
            'filters.server' => 'string',
            'filters.name' => 'string',
            'filters.short_name' => 'string',
            'filters.address' => 'string',
            'filters.port' => 'integer',
            'filters.active' => 'boolean',
            'filters.invisible' => 'boolean',
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

        return GameServerResource::collection(
            $this->indexQuery(GameServer::class)
        );
    }
}
