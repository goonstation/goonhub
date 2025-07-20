<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\PlayerMedalResource;
use App\Models\Medal;
use App\Models\Player;
use App\Models\PlayerMedal;
use App\Rules\DateRange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @tags Player Medals
 */
class PlayerMedalsController extends Controller
{
    /**
     * List
     *
     * List filtered and paginated medals
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<PlayerMedalResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.player_id' => 'int',
            'filters.ckey' => 'string',
            'filters.medal_id' => 'int',
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

        $query = PlayerMedal::with(['medal']);

        if ($request->input('sort_by') === 'medal_title') {
            $query = $query->withAggregate('medal', 'title');
        }

        return PlayerMedalResource::collection(
            $query->indexFilterPaginate()
        );
    }

    /**
     * Show
     *
     * Show a medal for a player
     */
    // public function show(Request $request, Player $player)
    // {
    //     $data = $this->validate($request, [
    //         /** The title of the medal */
    //         'medal' => 'required|string|exists:medals,title',
    //     ]);

    //     $medal = Medal::where('title', $data['medal'])->first();

    //     $playerMedal = PlayerMedal::with('medal')
    //         ->where('player_id', $player->id)
    //         ->where('medal_id', $medal->id)
    //         ->firstOrFail();

    //     return new PlayerMedalResource($playerMedal);
    // }

    /**
     * Has
     *
     * Determine if a player has a medal
     */
    public function has(Request $request, string $player)
    {
        $data = $this->validate($request, [
            /** The title of the medal */
            'medal' => 'required|string',
        ]);

        $playerId = null;
        if (ctype_digit($player)) {
            $playerId = (int) $player;
        } else {
            $playerRecord = Player::where('ckey', $player)->first();
            if ($playerRecord) {
                $playerId = $playerRecord->id;
            }
        }

        if (! $playerId) {
            return response()->json(['message' => 'Unable to locate that player'], 400);
        }

        $playerHasMedal = PlayerMedal::where('player_id', $playerId)
            ->whereRelation('medal', 'title', $data['medal'])
            ->exists();

        return ['data' => [
            /** @var bool */
            'has_medal' => $playerHasMedal,
        ]];
    }

    /**
     * Add
     *
     * Add a medal for a player
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'player_id' => 'required_without:ckey|nullable|integer|exists:players,id',
            'ckey' => 'required_without:player_id|nullable|alpha_num',
            'medal' => 'required|string|exists:medals,title',
            'round_id' => 'nullable|integer|exists:game_rounds,id',
        ]);

        $medal = Medal::where('title', $data['medal'])->first();

        $playerId = isset($data['player_id']) ? $data['player_id'] : null;
        if (! $playerId && isset($data['ckey'])) {
            // If we don't have a player_id, we are likely requested via the "Copy Medals" ingame verb
            // which doesn't require a logged in target player
            $player = Player::firstOrCreate([
                'ckey' => $data['ckey'],
            ]);
            $playerId = $player->id;
        }

        $playerMedal = PlayerMedal::firstOrCreate(
            [
                'player_id' => $playerId,
                'medal_id' => $medal->id,
            ],
            [
                'round_id' => isset($data['round_id']) ? $data['round_id'] : null,
            ]
        );

        if (! $playerMedal->wasRecentlyCreated) {
            return response()->json(['message' => 'That player already has that medal'], 409);
        }

        return new PlayerMedalResource($playerMedal);
    }

    /**
     * Delete
     *
     * Delete a medal for a player
     */
    public function destroy(Request $request)
    {
        $data = $this->validate($request, [
            'player_id' => 'required_without:ckey|nullable|integer|exists:players,id',
            'ckey' => 'required_without:player_id|nullable|alpha_num',
            'medal' => 'required|string|exists:medals,title',
        ]);

        $playerId = isset($data['player_id']) ? $data['player_id'] : null;
        if (! $playerId && isset($data['ckey'])) {
            $player = Player::where('ckey', $data['ckey'])->first();
            if (! $player) {
                return response()->json(['message' => 'Unable to locate that player'], 400);
            }
            $playerId = $player->id;
        }

        $medal = Medal::where('title', $data['medal'])->first();

        PlayerMedal::where('player_id', $playerId)
            ->where('medal_id', $medal->id)
            ->delete();

        return ['message' => 'Medal removed'];
    }

    /**
     * Transfer
     *
     * Transfer medals from one player to another
     */
    public function transfer(Request $request)
    {
        $data = $this->validate($request, [
            'source_ckey' => 'required|alpha_num',
            'target_ckey' => 'required|alpha_num|different:source_ckey',
        ]);

        $sourcePlayer = Player::where('ckey', $data['source_ckey'])->first();
        if (! $sourcePlayer) {
            return response()->json(['message' => 'Unable to locate source player'], 400);
        }

        $sourceMedals = PlayerMedal::where('player_id', $sourcePlayer->id)->get();
        if ($sourceMedals->isEmpty()) {
            return response()->json(['message' => 'Source player has no medals'], 400);
        }

        $targetPlayer = Player::where('ckey', $data['target_ckey'])->first();
        if (! $targetPlayer) {
            // Might as well just make a brand new player
            $targetPlayer = Player::create(['ckey' => $data['target_ckey']]);
        }

        $targetMedals = PlayerMedal::where('player_id', $targetPlayer->id)->get();

        $medalsToInsert = [];
        $now = Carbon::now()->toDateTimeString();
        foreach ($sourceMedals as $sourceMedal) {
            if (! $targetMedals->contains('medal_id', $sourceMedal->medal_id)) {
                // Only insert if the target doesn't already have this medal
                $medalsToInsert[] = [
                    'player_id' => $targetPlayer->id,
                    'medal_id' => $sourceMedal->medal_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        PlayerMedal::insert($medalsToInsert);
        PlayerMedal::whereIn('id', $sourceMedals->pluck('id'))->delete();

        return ['message' => 'Medals transferred'];
    }
}
