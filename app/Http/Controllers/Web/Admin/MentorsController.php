<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerMentor;
use Illuminate\Http\Request;

class MentorsController extends Controller
{
    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        PlayerMentor::whereIn('id', $data['ids'])->delete();

        return ['message' => 'Mentors removed successfully'];
    }

    public function toggle(Request $request, Player $player)
    {
        if ($player->is_mentor) {
            $player->mentor()->delete();

            return ['message' => 'Mentor removed successfully'];
        }

        $player->mentor()->create(['player_id' => $player->id]);

        return ['message' => 'Mentor added successfully'];
    }

    public function bulkToggle(Request $request)
    {
        $data = $request->validate([
            'player_ids' => 'required|array|exists:players,id',
            'make_mentor' => 'required|boolean',
        ]);

        if ($data['make_mentor']) {
            $existingMentors = PlayerMentor::whereIn('player_id', $data['player_ids'])->get();
            $nonMentors = collect($data['player_ids'])->diff($existingMentors->pluck('player_id'));
            PlayerMentor::insert(
                $nonMentors->map(fn ($id) => ['player_id' => $id, 'created_at' => now(), 'updated_at' => now()])
                    ->toArray()
            );
        } else {
            PlayerMentor::whereIn('player_id', $data['player_ids'])->delete();
        }

        return ['message' => 'Mentors updated successfully'];
    }
}
