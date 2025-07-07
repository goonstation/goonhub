<?php

namespace App\Traits;

use App\Jobs\RecordPlayerConnection;
use App\Models\Player;

trait ManagesPlayers
{
    private function loginPlayer(Player $player, array $data)
    {
        if (isset($data['byond_major'])) {
            $player->byond_major = $data['byond_major'];
        }
        if (isset($data['byond_minor'])) {
            $player->byond_minor = $data['byond_minor'];
        }

        Player::withoutAuditing(function () use ($player) {
            return $player->save();
        });

        RecordPlayerConnection::dispatch($player->id, $data);

        return $player;
    }
}
