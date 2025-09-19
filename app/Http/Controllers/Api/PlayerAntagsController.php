<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlayerAntagResource;
use App\Models\PlayerAntag;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Player Antags')]
class PlayerAntagsController extends Controller
{
    /**
     * Add
     *
     * Add a player antagonist for a given round
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'player_id' => 'required|integer|exists:players,id',
            'round_id' => 'required|integer|exists:game_rounds,id',
            'antag_role' => 'required|string',
            'late_join' => 'nullable|boolean',
            'weight_exempt' => 'nullable|string',
        ]);

        $antagPick = new PlayerAntag;
        $antagPick->player_id = $data['player_id'];
        $antagPick->round_id = $data['round_id'];
        $antagPick->antag_role = $data['antag_role'];
        $antagPick->late_join = isset($data['late_join']) ? (bool) $data['late_join'] : false;
        $antagPick->weight_exempt = isset($data['weight_exempt']) ? $data['weight_exempt'] : null;
        $antagPick->save();

        return new PlayerAntagResource($antagPick);
    }
}
