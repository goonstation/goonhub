<?php

namespace App\Traits;

use App\Http\Requests\PlayerNotes\StoreRequest;
use App\Http\Resources\PlayerNoteResource;
use App\Models\Player;
use App\Models\PlayerNote;

trait ManagesPlayerNotes
{
    private function addNote(StoreRequest $request)
    {
        $data = $request->validated();
        $gameAdmin = $request->getGameAdmin();
        $player = Player::where('ckey', $data['ckey'])->first();

        $note = new PlayerNote;
        $note->game_admin_id = $gameAdmin ? $gameAdmin->id : null;
        $note->round_id = isset($data['round_id']) ? $data['round_id'] : null;
        $note->server_id = isset($data['server_id']) ? $data['server_id'] : null;
        if ($player) {
            $note->player_id = $player->id;
        } else {
            $note->ckey = $data['ckey'];
        }
        $note->note = $data['note'];
        $note->save();

        return new PlayerNoteResource($note);
    }

    private function updateNote(StoreRequest $request, PlayerNote $note)
    {
        $data = $request->validated();

        $updateData = [];
        $gameAdmin = $request->getGameAdmin();
        if ($gameAdmin) {
            $updateData['game_admin_id'] = $gameAdmin->id;
        }

        $player = Player::where('ckey', $data['ckey'])->first();
        if ($player) {
            $updateData['player_id'] = $player->id;
        } else {
            $updateData['ckey'] = $data['ckey'];
        }

        if (isset($data['server_id'])) {
            $updateData['server_id'] = $data['server_id'];
        }
        $updateData['note'] = $data['note'];
        $note->update($updateData);

        return new PlayerNoteResource($note);
    }
}
