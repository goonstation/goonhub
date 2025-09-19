<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\PlayerMetadataResource;
use App\Models\PlayerMetadata;
use App\Rules\DateRange;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

#[Group('Player Metadata')]
class PlayerMetadataController extends Controller
{
    /**
     * List
     *
     * List paginated and filtered player meta data
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<PlayerMetadataResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.ckey' => 'string',
            'filters.metadata' => 'string',
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

        return PlayerMetadataResource::collection(
            PlayerMetadata::with('player')
                ->indexFilterPaginate()
        );
    }

    /**
     * Get By Player
     *
     * Get all the metadata associated with a ckey
     */
    public function getByPlayer(string $ckey)
    {
        $metadata = PlayerMetadata::whereRelation('player', 'ckey', $ckey)
            ->select('metadata')
            ->get();

        return [
            /** @var array{string} */
            'data' => $metadata->pluck('metadata'),
        ];
    }

    /**
     * Get By Metadata
     *
     * Get all the ckeys associated with a piece of metadata
     */
    public function getByData(string $metadata)
    {
        $metadata = PlayerMetadata::with('player:id,ckey')
            ->where('metadata', $metadata)
            ->select('player_id', 'metadata')
            ->distinct('player_id')
            ->get();

        return [
            /** @var array{string} */
            'data' => $metadata->pluck('player.ckey'),
        ];
    }

    /**
     * Add
     *
     * Add player metadata
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'player_id' => 'required|integer|exists:players,id',
            'metadata' => 'required|string',
        ]);

        $metadata = new PlayerMetadata;
        $metadata->player_id = $data['player_id'];
        $metadata->metadata = $data['metadata'];
        $metadata->save();

        return new PlayerMetadataResource($metadata);
    }

    /**
     * Delete By Player
     *
     * Delete all metadata associated with a specific player
     */
    public function destroyByPlayer(string $ckey)
    {
        PlayerMetadata::whereRelation('player', 'ckey', $ckey)->delete();

        return ['message' => 'Metadata removed'];
    }

    /**
     * Delete By Metadata
     *
     * Delete all matching metadata items
     */
    public function destroyByData(string $metadata)
    {
        PlayerMetadata::where('metadata', $metadata)->delete();

        return ['message' => 'Metadata removed'];
    }
}
