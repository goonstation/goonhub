<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

#[Group('Player Playtime')]
class PlayerPlaytimeController extends Controller
{
    /**
     * Add playtime in bulk
     *
     * Record playtime for a list of players
     */
    public function storeBulk(Request $request)
    {
        /* e.g.
        players = [
            [
                id: 123,
                seconds_played: 456
            ]
        ]
        */
        $data = $request->validate([
            'players' => 'required|array',
            'players.*.id' => 'required|integer',
            'players.*.seconds_played' => 'required|integer',
            'server_id' => 'required|string',
        ]);

        $playerIds = Player::select('id')
            ->whereIn('id', Arr::pluck($data['players'], 'id'))
            ->get()
            ->pluck('id')
            ->toArray();

        $values = [];
        $valuesSql = '';
        $recordedPlayerIds = [];
        foreach ($data['players'] as $key => $player) {
            if (! in_array($player['id'], $playerIds)) {
                continue;
            }
            if (in_array($player['id'], $recordedPlayerIds)) {
                continue;
            }
            $recordedPlayerIds[] = $player['id'];
            array_push($values, $player['id'], $player['seconds_played'], $data['server_id']);
            $valuesSql .= '(?, ?, ?), ';
        }

        $valuesSql = substr($valuesSql, 0, -2);
        DB::insert("
            INSERT INTO player_playtime (player_id, seconds_played, server_id)
            VALUES $valuesSql
            ON CONFLICT ON CONSTRAINT player_playtime_player_id_server_id_unique
            DO UPDATE SET seconds_played = player_playtime.seconds_played + EXCLUDED.seconds_played;
        ", $values);

        return ['message' => 'Success'];
    }
}
