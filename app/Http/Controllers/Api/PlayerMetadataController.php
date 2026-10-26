<?php

namespace App\Http\Controllers\Api;

use App\Attributes\HasDateRangeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerMetadata\IndexRequest;
use App\Http\Resources\PlayerMetadataResource;
use App\Models\PlayerMetadata;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
    #[
        HasDateRangeFilter(name: 'created_at'),
        HasDateRangeFilter(name: 'updated_at'),
    ]
    public function index(IndexRequest $request)
    {
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
     * Get By Metadata Bulk
     *
     * Get the ckeys associated with multiple pieces of metadata at once
     */
    public function getByDataBulk(Request $request)
    {
        $data = $request->validate([
            'metadata' => ['required', 'array', 'min:1', 'max:25'],
            'metadata.*' => ['required', 'string'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $values = $data['metadata'];
        $limit = $data['limit'] ?? 50;

        // Deduplicate player rows before counting
        $distinct = PlayerMetadata::whereIn('metadata', $values)
            ->whereNotNull('player_id')
            ->select('metadata', 'player_id')
            ->distinct();
        $ranked = DB::query()
            ->fromSub($distinct, 'metadata_players')
            ->join('players', 'players.id', '=', 'metadata_players.player_id')
            ->select('metadata_players.metadata', 'players.ckey')
            ->selectRaw('COUNT(*) OVER (PARTITION BY metadata_players.metadata) AS player_count')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY metadata_players.metadata ORDER BY players.ckey) AS metadata_rank');
        $matches = DB::query()
            ->fromSub($ranked, 'ranked_metadata_players')
            ->where('metadata_rank', '<=', $limit)
            ->orderBy('metadata_rank')
            ->get()
            ->groupBy('metadata');

        $result = [];
        foreach ($values as $value) {
            $players = $matches->get($value, collect());
            $result[$value] = [
                'count' => (int) ($players->first()->player_count ?? 0),
                'ckeys' => $players->pluck('ckey')->all(),
            ];
        }

        return [
            /** @var array<string, array{count: int, ckeys: array<int, string>}> */
            'data' => (object) $result,
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
     * Add Bulk
     *
     * Add multiple pieces of metadata to a player at once, skipping any they already have
     */
    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'player_id' => 'required|integer|exists:players,id',
            'metadata' => ['required', 'array', 'min:1', 'max:25'],
            'metadata.*' => ['required', 'string', 'distinct'],
        ]);

        $existing = PlayerMetadata::where('player_id', $data['player_id'])
            ->whereIn('metadata', $data['metadata'])
            ->pluck('metadata')
            ->all();
        $now = now();
        $insertData = [];
        foreach (array_diff($data['metadata'], $existing) as $metadata) {
            $insertData[] = [
                'player_id' => $data['player_id'],
                'metadata' => $metadata,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        PlayerMetadata::insert($insertData);

        return ['message' => 'Added metadata'];
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
