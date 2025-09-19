<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlayerParticipationResource;
use App\Models\PlayerParticipation;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

use function Sentry\captureMessage;

#[Group('Player Participations')]
class PlayerParticipationsController extends Controller
{
    /**
     * Add
     *
     * Add a player participation for a given round
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'player_id' => 'required|integer|exists:players,id',
            'round_id' => 'required|integer|exists:game_rounds,id',
            'job' => 'nullable|string',
        ]);

        $participation = new PlayerParticipation;
        $participation->player_id = $data['player_id'];
        $participation->round_id = $data['round_id'];
        $participation->job = isset($data['job']) ? $data['job'] : null;
        $participation->save();

        return new PlayerParticipationResource($participation);
    }

    /**
     * Add Bulk
     *
     * Add multiple participations at once
     */
    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'players' => 'required|array',
            'players.*.player_id' => 'sometimes|nullable|integer',
            'players.*.job' => 'sometimes|nullable|string',
            'round_id' => 'required|integer|exists:game_rounds,id',
        ]);

        $insertData = [];
        foreach ($data['players'] as $player) {
            if (! $player['player_id']) {
                captureMessage('Invalid data during player participations storeBulk', null, $player);

                continue;
            }
            $insertData[] = [
                'player_id' => $player['player_id'],
                'round_id' => $data['round_id'],
                'job' => isset($player['job']) ? $player['job'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        PlayerParticipation::insert($insertData);

        return ['message' => 'Added participations'];
    }
}
