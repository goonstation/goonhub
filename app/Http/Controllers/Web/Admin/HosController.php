<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerHos;
use Illuminate\Http\Request;

class HosController extends Controller
{
    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        PlayerHos::whereIn('id', $data['ids'])->delete();

        return ['message' => 'Heads of Security removed successfully'];
    }

    public function toggle(Request $request, Player $player)
    {
        if ($player->is_hos) {
            $player->hos()->delete();

            return ['message' => 'Head of Security removed successfully'];
        }

        $player->hos()->create(['player_id' => $player->id]);

        return ['message' => 'Head of Security added successfully'];
    }

    public function bulkToggle(Request $request)
    {
        $data = $request->validate([
            'player_ids' => 'required|array|exists:players,id',
            'make_hos' => 'required|boolean',
        ]);

        if ($data['make_hos']) {
            $existingHos = PlayerHos::whereIn('player_id', $data['player_ids'])->get();
            $nonHos = collect($data['player_ids'])->diff($existingHos->pluck('player_id'));
            PlayerHos::insert(
                $nonHos->map(fn ($id) => ['player_id' => $id, 'created_at' => now(), 'updated_at' => now()])
                    ->toArray()
            );
        } else {
            PlayerHos::whereIn('player_id', $data['player_ids'])->delete();
        }

        return ['message' => 'Heads of Security updated successfully'];
    }
}
