<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions\GameRoundPermissions;
use App\Helpers\HasAnyAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameRoundResource;
use App\Models\GameRound;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Carbon;

#[Group('Game Rounds')]
class GameRoundsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasAnyAbility::using(GameRoundPermissions::ADD, only: ['store']),
            HasAnyAbility::using(GameRoundPermissions::UPDATE, only: ['update']),
            HasAnyAbility::using(GameRoundPermissions::END, only: ['endRound']),
        ];
    }

    /**
     * Add
     *
     * Start a new game round
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'server_id' => 'required|string',
            'map' => 'required|string',
            'rp_mode' => 'nullable|boolean',
            'test_merges' => 'nullable|array',
            'test_merges.*' => 'sometimes|required|numeric',
        ]);

        $gameRound = new GameRound;
        $gameRound->server_id = $data['server_id'];
        $gameRound->map = $data['map'];
        $gameRound->rp_mode = empty($data['rp_mode']) ? false : $data['rp_mode'];
        $gameRound->test_merges = isset($data['test_merges']) ? json_encode($data['test_merges']) : null;
        $gameRound->save();

        $previousRound = GameRound::where('server_id', $data['server_id'])
            ->where('id', '!=', $gameRound->id)
            ->latest()
            ->first();
        if ($previousRound && ! $previousRound->ended_at) {
            // Previous round didn't tell the API that it ended
            // (maybe crashed, or restarted outside of game functions)
            $previousRound->ended_at = Carbon::now();
            $previousRound->crashed = true;
            $previousRound->update();
        }

        return new GameRoundResource($gameRound);
    }

    /**
     * Update
     *
     * Update a game round. This should be used when game round data we care about is set after the start of the round.
     */
    public function update(Request $request, GameRound $gameRound)
    {
        $data = $request->validate([
            'game_type' => 'string',
        ]);
        $gameRound->game_type = isset($data['game_type']) ? $data['game_type'] : null;
        $gameRound->update();

        return new GameRoundResource($gameRound);
    }

    /**
     * End
     *
     * End a game round.
     */
    public function endRound(Request $request, GameRound $gameRound)
    {
        $data = $request->validate([
            'crashed' => 'required|boolean',
        ]);
        $gameRound->crashed = isset($data['crashed']) ? (bool) $data['crashed'] : false;
        $gameRound->ended_at = Carbon::now();
        $gameRound->update();

        return new GameRoundResource($gameRound);
    }
}
